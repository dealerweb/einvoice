<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

/**
 * Finds the paths of compiled expressions whose value depends on the document alone - "//ram:IncludedSupplyChain
 * TradeLineItem[...]" in a rule on each line, document('...codedb.xml')/codedb/cl[@id=15] - and marks them for the
 * evaluator to compute once per document: ['memo', number, path, whether it reads the document of the focus].
 * Saxon-HE evaluates them anew for each node, which on a large invoice makes a rule on each line take a time growing
 * with the square of the lines; the result is the same.
 *
 * A path qualifies when it reads no variable of the frame - its predicates and steps neither - and reads the focus
 * (the context item, its position, the size) only through "/". Of nested ones only the outermost is marked.
 *
 * @internal
 */
final class Invariants
{
    private const FOCUS = 1;
    private const ROOT = 2;
    private const FRAME = 4;

    /** The functions that read the context item when called without argument (Evaluator::call()). */
    private const IMPLICIT = ['string', 'string-length', 'normalize-space', 'number', 'name', 'local-name', 'namespace-uri', 'root'];

    private const PATHS = ['slash', 'filter', 'docOrder'];

    private int $count = 0;

    /**
     * @template T of array
     *
     * @param T $tree compiled rules, functions or variables
     *
     * @return T the tree with its invariant paths marked
     */
    public function mark(array $tree): array
    {
        /** @var T */
        return $this->visit($tree)[0];
    }

    /**
     * @param array<mixed> $e an expression, or a part of the compiled rule set around expressions
     *
     * @return array{0: array<mixed>, 1: int, 2: bool} the part with its invariant paths marked, what it reads, whether
     *                                                   it is an invariant path itself - to be marked where its parent
     *                                                   is none
     */
    private function visit(array $e): array
    {
        $type = isset($e[0]) && is_string($e[0]) ? $e[0] : null;
        switch ($type) {
            case 'dot':
            case 'axis':
            case 'attVal':
            case 'isLast':
                return [$e, self::FOCUS, false];
            case 'root':
                return [$e, self::ROOT, false];
            case 'var':
                return [$e, self::FRAME, false];
        }

        $flags = 0;
        $candidates = [];
        foreach ($e as $index => $part) {
            if (! is_array($part)) {
                continue;
            }
            [$marked, $partFlags, $candidate] = $this->visit($part);
            $e[$index] = $marked;
            if ($candidate) {
                $candidates[$index] = ($partFlags & self::ROOT) !== 0;
            }
            // a step and a predicate have a focus of their own: of what they read, only the variables count here
            if ($index === 2 && ($type === 'slash' || $type === 'filter')) {
                $partFlags &= self::FRAME;
            }
            $flags |= $partFlags;
        }
        if ($type === 'fn' && (in_array($e[1], ['position', 'last'], true) || ($e[2] === [] && in_array($e[1], self::IMPLICIT, true)))) {
            $flags |= self::FOCUS;
        }

        $candidate = in_array($type, self::PATHS, true) && ($flags & (self::FOCUS | self::FRAME)) === 0;
        if (! $candidate) {
            foreach ($candidates as $index => $rooted) {
                $e[$index] = ['memo', $this->count++, $e[$index], $rooted];
            }
        }

        return [$e, $flags, $candidate];
    }
}
