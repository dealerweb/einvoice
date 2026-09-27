<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Kosit;

use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Rules;
use DOMAttr;
use DOMCharacterData;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMNameSpaceNode;
use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;
use RuntimeException;

/**
 * Executes a compiled KoSIT mapping (resources/compiled/ubl-invoice.php, ubl-creditnote.php, cii.php)
 * against an invoice and produces the EN 16931 intermediate document of KoSIT
 * (namespace urn:ce.eu:en16931:2017:xoev-de:kosit:standard:xrechnung-1).
 *
 * The result is identical to running the original XSLT with Saxon - proven for the complete KoSIT test suite. Two
 * deliberate deviations: an impossible date is reported as illegal where Saxon aborts (date()), and an indicator
 * written as "1" or "0" counts like "true" and "false" (canonicalizeIndicators()).
 *
 * @internal
 */
final class Transformer
{
    /** Namespace of the KoSIT intermediate document (prefix "xr" in the stylesheets). */
    public const XR_NAMESPACE = 'urn:ce.eu:en16931:2017:xoev-de:kosit:standard:xrechnung-1';

    private const VALUE_TYPES = [
        'text', 'date', 'identifier', 'identifier-with-scheme', 'code', 'amount', 'percentage',
        'binary_object', 'unit_price_amount', 'quantity', 'document_reference',
    ];

    private readonly DOMXPath $xpath;

    private readonly NodeIndex $index;

    private DOMDocument $output;

    /** @var array<string, array<string, true>> match pattern => node keys of all matching nodes */
    private array $patterns = [];

    /** @var array<string, DOMNode> source nodes whose value ended up in the output, by node key */
    private array $consumed = [];

    /**
     * @param array<string, mixed> $program compiled stylesheet
     */
    public function __construct(private readonly array $program, private readonly DOMDocument $source)
    {
        $this->xpath = new DOMXPath($source);
        foreach ($program['namespaces'] as $prefix => $uri) {
            $this->xpath->registerNamespace($prefix, $uri);
        }
        $this->index = new NodeIndex($source);
    }

    /**
     * Keys, source paths and document order of the nodes of the source document.
     */
    public function index(): NodeIndex
    {
        return $this->index;
    }

    public function transform(): DOMDocument
    {
        $this->output = new DOMDocument('1.0', 'UTF-8');
        $this->consumed = [];

        $originals = $this->canonicalizeIndicators();
        try {
            $root = $this->source->documentElement;
            if ($root === null || ! $this->matches($this->program['root']['match'], $root)) {
                throw new UnsupportedDocument('The document root does not match ' . $this->program['source']);
            }

            $this->execute($this->program['root']['body'], $root, 1, 1, [], $this->output);
        } finally {
            // The document stays as delivered - coverage and labels read it afterwards.
            foreach ($originals as [$text, $value]) {
                $text->data = $value;
            }
        }
        $this->output->normalizeDocument();

        return $this->output;
    }

    /**
     * Deliberate deviation from KoSIT: the stylesheets compare indicators (xs:boolean) as text with "true" and
     * "false". "1" and "0" are just as valid, and the validation of EN 16931 reads them as boolean - but in the
     * mapping such an allowance or charge would drop out of the model. For the run, every indicator the program
     * compares (the compiled mapping lists them in "indicators") is written the way the mapping expects it
     * (Rules::indicator); transform() restores the original text afterwards.
     *
     * @return list<array{DOMText, string}> changed text nodes with their original content
     */
    private function canonicalizeIndicators(): array
    {
        $originals = [];
        foreach ($this->program['indicators'] ?? [] as $name) {
            foreach ($this->select('//' . $name, $this->source) as $element) {
                $value = Rules::indicator($element->textContent);
                $text = $element->firstChild;
                // An indicator is a single text; anything else is left to the mapping as it is.
                if ($value === null || $element->childNodes->length !== 1 || ! $text instanceof DOMText) {
                    continue;
                }

                $canonical = $value ? 'true' : 'false';
                if ($text->data !== $canonical) {
                    $originals[] = [$text, $text->data];
                    $text->data = $canonical;
                }
            }
        }

        return $originals;
    }

    /**
     * Source nodes (elements and attributes) whose value was written to the output.
     *
     * @return array<string, DOMNode>
     */
    public function consumed(): array
    {
        return $this->consumed;
    }

    /**
     * @param list<array<string, mixed>> $instructions
     * @param array<string, array{nodes?: list<DOMNode>, fragment?: DOMDocumentFragment}> $vars
     */
    private function execute(array $instructions, DOMNode $context, int $position, int $size, array $vars, DOMNode $parent): void
    {
        foreach ($instructions as $instruction) {
            switch ($instruction['op']) {
                case 'text':
                    $this->appendText($parent, $instruction['value']);
                    break;

                case 'element':
                    $element = $this->output->createElementNS(self::XR_NAMESPACE, 'xr:' . $instruction['name']);
                    $parent->appendChild($element);
                    $this->execute($instruction['body'], $context, $position, $size, $vars, $element);
                    break;

                case 'attribute':
                    if (isset($instruction['body'])) {
                        $fragment = $this->output->createDocumentFragment();
                        $this->execute($instruction['body'], $context, $position, $size, $vars, $fragment);
                        $value = $fragment->textContent;
                    } else {
                        $value = $this->string($instruction['value'], $context, $position, $size, $vars);
                    }
                    $this->setAttribute($parent, $instruction['name'], $value);
                    break;

                case 'apply':
                    $this->applyTemplates(
                        $instruction['mode'],
                        $this->nodes($instruction['select'], $context, $position, $size, $vars),
                        $parent
                    );
                    break;

                case 'call':
                    $params = [];
                    foreach ($instruction['params'] as $name => $select) {
                        $params[$name] = $this->nodes($select, $context, $position, $size, $vars);
                    }
                    $this->call($instruction['name'], $context, $position, $size, $params, $parent);
                    break;

                case 'if':
                    if ($this->boolean($instruction['test'], $context, $position, $size, $vars)) {
                        $this->execute($instruction['body'], $context, $position, $size, $vars, $parent);
                    }
                    break;

                case 'choose':
                    $chosen = $instruction['otherwise'] ?? [];
                    foreach ($instruction['when'] as $when) {
                        if ($this->boolean($when['test'], $context, $position, $size, $vars)) {
                            $chosen = $when['body'];
                            break;
                        }
                    }
                    $this->execute($chosen, $context, $position, $size, $vars, $parent);
                    break;

                case 'value-of':
                    $this->appendText($parent, $this->string($instruction['select'], $context, $position, $size, $vars, true));
                    break;

                case 'variable':
                    if (isset($instruction['select'])) {
                        $vars[$instruction['name']] = ['nodes' => $this->nodes($instruction['select'], $context, $position, $size, $vars)];
                    } else {
                        $fragment = $this->output->createDocumentFragment();
                        $this->execute($instruction['body'], $context, $position, $size, $vars, $fragment);
                        $vars[$instruction['name']] = ['fragment' => $fragment];
                    }
                    break;

                case 'sequence':
                    $fragment = $vars[$instruction['var']]['fragment'] ?? null;
                    if ($fragment instanceof DOMDocumentFragment && $fragment->hasChildNodes()) {
                        $parent->appendChild($fragment);
                    }
                    break;

                case 'special':
                    SpecialCases::run($instruction['key'], $this, $context, $vars, $parent);
                    break;

                default:
                    throw new RuntimeException("Unknown instruction {$instruction['op']}");
            }
        }
    }

    /**
     * xsl:apply-templates with a mode, including XSLT's built-in rules for nodes without template.
     *
     * @param list<DOMNode> $nodes
     */
    public function applyTemplates(string $mode, array $nodes, DOMNode $parent): void
    {
        $size = count($nodes);

        foreach ($nodes as $index => $node) {
            $template = $this->template($mode, $node);

            if ($template !== null) {
                $this->execute($template['body'], $node, $index + 1, $size, [], $parent);
                continue;
            }

            // Built-in template rules: elements recurse into their children, text and attributes are copied.
            if ($node instanceof DOMElement) {
                $this->applyTemplates($mode, iterator_to_array($node->childNodes, false), $parent);
            } elseif ($node instanceof DOMCharacterData && $node->nodeType !== XML_COMMENT_NODE) {
                $this->appendText($parent, $node->data);
                if ($node->parentNode !== null) {
                    $this->consume($node->parentNode);
                }
            } elseif ($node instanceof DOMAttr) {
                $this->appendText($parent, $node->value);
                $this->consume($node);
            }
        }
    }

    /**
     * Evaluates an XPath 1.0 expression relative to a node (document order, as DOMXPath returns it).
     *
     * @return list<DOMNode>
     */
    public function select(string $xpath, DOMNode $context): array
    {
        // Prefixes resolve with the namespaces of the stylesheet only, as in XSLT - never with the
        // prefixes an invoice declares itself (registerNodeNS false).
        $result = $this->xpath->query($xpath, $context, false);
        if ($result === false) {
            throw new RuntimeException("Invalid XPath expression: $xpath");
        }

        return self::domNodes($result);
    }

    /**
     * Evaluates a relative path for several nodes: union of the results, duplicates removed, document order.
     *
     * @param list<DOMNode> $nodes
     * @return list<DOMNode>
     */
    public function selectEach(string $xpath, array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            foreach ($this->select($xpath, $node) as $found) {
                $result[$this->key($found)] = $found;
            }
        }

        return $this->index->documentOrder(array_values($result));
    }

    /**
     * Creates an output element with the KoSIT xr:id and xr:src attributes.
     */
    public function element(DOMNode $parent, string $name, string $id, string $src): DOMElement
    {
        $element = $this->output->createElementNS(self::XR_NAMESPACE, 'xr:' . $name);
        $element->setAttributeNS(self::XR_NAMESPACE, 'xr:id', $id);
        $element->setAttributeNS(self::XR_NAMESPACE, 'xr:src', $src);
        $parent->appendChild($element);

        return $element;
    }

    public function appendText(DOMNode $parent, string $text): void
    {
        if ($text !== '') {
            $parent->appendChild($this->output->createTextNode($text));
        }
    }

    /**
     * XPath string value of a node.
     */
    public function stringValue(DOMNode $node): string
    {
        return $node instanceof DOMAttr ? $node->value : (string) $node->textContent;
    }

    public function consume(DOMNode $node): void
    {
        $this->consumed[$this->key($node)] = $node;
    }

    /**
     * xr:src-path() of common-xr.xsl, see NodeIndex::path().
     */
    public function srcPath(DOMNode $node): string
    {
        return $this->index->path($node);
    }

    /**
     * Unique key of a source node (DOMNode objects are not identity-stable in PHP), see NodeIndex::key().
     */
    public function key(DOMNode $node): string
    {
        return $this->index->key($node);
    }

    /**
     * The date template of common-xr.xsl: YYYYMMDD or YYYY-MM-DD becomes YYYY-MM-DD.
     */
    public function date(string $value): string
    {
        $compact = str_replace('-', '', $value);
        $normalized = trim(preg_replace('/[\x20\x09\x0D\x0A]+/', ' ', $compact) ?? $compact, "\x20\x09\x0D\x0A");

        $year = mb_substr($normalized, 0, 4);
        $month = mb_substr($normalized, 4, 2);
        $day = mb_substr($normalized, 6, 2);

        $year = preg_match('/^[0-9]{4}/', $year) && (int) $year > 0 ? (int) $year : 0;
        $month = preg_match('/^[0-9]{2}/', $month) && (int) $month > 0 && (int) $month < 13 ? (int) $month : 0;
        $day = preg_match('/^[0-9]{2}/', $day) && (int) $day > 0 && (int) $day < 32 ? (int) $day : 0;

        // Saxon aborts on impossible dates like 2024-02-30; we report them like any other illegal date.
        if ($year > 0 && $month > 0 && $day > 0 && checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        return 'ILLEGAL DATE FORMAT of "' . $value . '".';
    }

    /**
     * @param array<string, list<DOMNode>> $params
     */
    private function call(string $name, DOMNode $context, int $position, int $size, array $params, DOMNode $parent): void
    {
        if (in_array($name, self::VALUE_TYPES, true)) {
            $this->valueType($name, $context, $parent, $params);

            return;
        }

        $template = $this->program['named'][$name] ?? null;
        if (! isset($template['body'])) {
            throw new RuntimeException("Named template $name is not available");
        }

        $vars = [];
        foreach ($params as $param => $nodes) {
            $vars[$param] = ['nodes' => $nodes];
        }
        $this->execute($template['body'], $context, $position, $size, $vars, $parent);
    }

    /**
     * The value-type templates of common-xr.xsl.
     *
     * @param array<string, list<DOMNode>> $params
     */
    private function valueType(string $name, DOMNode $context, DOMNode $parent, array $params): void
    {
        $this->consume($context);
        $element = $context instanceof DOMElement ? $context : null;

        switch ($name) {
            case 'date':
                $this->appendText($parent, $this->date($this->stringValue($context)));

                return;

            case 'identifier':
                if ($element !== null && ($element->hasAttribute('listID') || $element->hasAttribute('schemeID'))) {
                    $this->setAttribute($parent, 'scheme_identifier', $this->firstAttribute($element, ['listID', 'schemeID']));
                }
                if ($element !== null && ($element->hasAttribute('schemeVersionID') || $element->hasAttribute('listVersionID'))) {
                    $this->setAttribute($parent, 'scheme_version_identifier', $this->firstAttribute($element, ['listVersionID', 'schemeVersionID']));
                }
                break;

            case 'identifier-with-scheme':
                if ($element !== null && $element->hasAttribute('schemeID')) {
                    $scheme = $params['schemeID'][0] ?? null;
                    if ($scheme !== null) {
                        $this->consume($scheme);
                        $this->setAttribute($parent, 'scheme_identifier', $this->stringValue($scheme));
                    } else {
                        $this->setAttribute($parent, 'scheme_identifier', $this->firstAttribute($element, ['listID', 'schemeID']));
                    }
                }
                break;

            case 'binary_object':
                foreach (['mimeCode' => 'mime_code', 'filename' => 'filename'] as $attribute => $target) {
                    if ($element !== null && $element->hasAttribute($attribute)) {
                        $this->consume($element->getAttributeNode($attribute));
                        $this->setAttribute($parent, $target, $element->getAttribute($attribute));
                    }
                }
                break;
        }

        $this->appendText($parent, $this->stringValue($context));
    }

    /**
     * @param list<string> $names
     */
    private function firstAttribute(DOMElement $element, array $names): string
    {
        foreach ($names as $name) {
            if ($element->hasAttribute($name)) {
                $this->consume($element->getAttributeNode($name));

                return $element->getAttribute($name);
            }
        }

        return '';
    }

    private function setAttribute(DOMNode $parent, string $name, string $value): void
    {
        if (! $parent instanceof DOMElement) {
            return;
        }

        if (str_starts_with($name, 'xr:')) {
            $parent->setAttributeNS(self::XR_NAMESPACE, $name, $value);
        } else {
            $parent->setAttribute($name, $value);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function template(string $mode, DOMNode $node): ?array
    {
        $best = null;

        // Highest priority wins, among equals the last one in the stylesheet (XSLT conflict resolution).
        foreach ($this->program['modes'][$mode] ?? [] as $template) {
            if (($best === null || $template['priority'] >= $best['priority']) && $this->matches($template['match'], $node)) {
                $best = $template;
            }
        }

        return $best;
    }

    private function matches(string $pattern, DOMNode $node): bool
    {
        if (! isset($this->patterns[$pattern])) {
            $this->patterns[$pattern] = [];
            foreach ($this->select($pattern, $this->source) as $match) {
                $this->patterns[$pattern][$this->key($match)] = true;
            }
        }

        return isset($this->patterns[$pattern][$this->key($node)]);
    }

    /**
     * @param array<string, mixed> $expression
     * @param array<string, array{nodes?: list<DOMNode>, fragment?: DOMDocumentFragment}> $vars
     * @return list<DOMNode>
     */
    private function nodes(array $expression, DOMNode $context, int $position, int $size, array $vars): array
    {
        if (isset($expression['var'])) {
            return $vars[$expression['var']]['nodes'] ?? [];
        }

        return $this->select($this->positional($expression, $position, $size), $context);
    }

    /**
     * @param array<string, mixed> $expression
     * @param array<string, array{nodes?: list<DOMNode>, fragment?: DOMDocumentFragment}> $vars
     */
    private function boolean(array $expression, DOMNode $context, int $position, int $size, array $vars): bool
    {
        if (isset($expression['var'])) {
            $var = $vars[$expression['var']] ?? [];

            return isset($var['fragment']) ? $var['fragment']->hasChildNodes() : ($var['nodes'] ?? []) !== [];
        }

        if (isset($expression['literal'])) {
            return $expression['literal'] !== '';
        }

        return (bool) $this->xpath->evaluate('boolean(' . $this->positional($expression, $position, $size) . ')', $context, false);
    }

    /**
     * String value of an expression. A sequence of nodes is joined with a space (XSLT 2.0).
     *
     * @param array<string, mixed> $expression
     * @param array<string, array{nodes?: list<DOMNode>, fragment?: DOMDocumentFragment}> $vars
     */
    private function string(array $expression, DOMNode $context, int $position, int $size, array $vars, bool $consume = false): string
    {
        if (isset($expression['literal'])) {
            return $expression['literal'];
        }

        if (isset($expression['srcpath'])) {
            $nodes = $this->nodes($expression['srcpath'], $context, $position, $size, $vars);

            return $nodes === [] ? '' : $this->srcPath($nodes[0]);
        }

        if (isset($expression['var'])) {
            $var = $vars[$expression['var']] ?? [];
            if (isset($var['fragment'])) {
                return (string) $var['fragment']->textContent;
            }
            $nodes = $var['nodes'] ?? [];
        } else {
            $result = $this->xpath->evaluate($this->positional($expression, $position, $size), $context, false);
            if (! $result instanceof DOMNodeList) {
                return is_bool($result) ? ($result ? 'true' : 'false') : (string) $result;
            }
            $nodes = self::domNodes($result);
        }

        $values = [];
        foreach ($nodes as $node) {
            $values[] = $this->stringValue($node);
            if ($consume) {
                $this->consume($node);
            }
        }

        return implode(' ', $values);
    }

    /**
     * position() and last() refer to the node list a template was applied to - libxml does not
     * know that list, so the numbers are substituted (the compiler allows this outside predicates only).
     *
     * @param array<string, mixed> $expression
     */
    private function positional(array $expression, int $position, int $size): string
    {
        if (empty($expression['positional'])) {
            return $expression['xpath'];
        }

        return preg_replace(
            ['/\bposition\s*\(\s*\)/', '/\blast\s*\(\s*\)/'],
            [(string) $position, (string) $size],
            $expression['xpath'],
        );
    }

    /**
     * The nodes of an XPath result. A namespace node (namespace:: axis, which the mapping never selects) is no
     * DOMNode in PHP and is left out.
     *
     * @param DOMNodeList<DOMNode|DOMNameSpaceNode> $list
     * @return list<DOMNode>
     */
    private static function domNodes(DOMNodeList $list): array
    {
        $nodes = [];
        foreach ($list as $node) {
            if ($node instanceof DOMNode) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }
}
