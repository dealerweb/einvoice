<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

use Closure;

/**
 * A variable evaluated when it is first used, then kept - like Saxon evaluates the let variables of a rule: a
 * variable that no test uses never runs, so it can never fail.
 *
 * @internal
 */
final class Lazy
{
    /** @var list<mixed>|null */
    private ?array $value = null;

    /**
     * @param Closure(): list<mixed> $compute
     */
    public function __construct(private readonly Closure $compute) {}

    /**
     * @return list<mixed>
     */
    public function get(): array
    {
        return $this->value ??= ($this->compute)();
    }
}
