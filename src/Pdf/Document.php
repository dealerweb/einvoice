<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

use Closure;

/**
 * The objects of a PDF by their numbers and its trailer. Objects are read from the file when first asked for; what is
 * changed or added stays here until Writer writes the document anew.
 *
 * Objects added get negative numbers: no reference of the file has one, so an added object never takes the number
 * a reference to a missing object of the file names (Writer numbers every object anew).
 *
 * @internal
 */
final class Document
{
    /** @var array<int, mixed> objects read, changed or added, by number */
    private array $objects = [];

    /** @var array<int, true> objects being read - a stream whose Length refers to itself must not read it again */
    private array $reading = [];

    private int $added = 0;

    /**
     * @param Closure(int): mixed $read reads an object of the file by its number - null if the file has none
     */
    public function __construct(public Dictionary $trailer, private readonly Closure $read) {}

    public function object(int $number): mixed
    {
        if (array_key_exists($number, $this->objects)) {
            return $this->objects[$number];
        }
        // A reference back to an object being read (its own Length, directly or by way of others) is to nothing.
        if (isset($this->reading[$number])) {
            return null;
        }
        $this->reading[$number] = true;
        try {
            $value = ($this->read)($number);
        } finally {
            unset($this->reading[$number]);
        }

        return $this->objects[$number] = $value;
    }

    /**
     * The object a value refers to - the value itself if it is no reference; null for a reference to nothing.
     */
    public function resolve(mixed $value): mixed
    {
        for ($steps = 0; $value instanceof Reference && $steps < 32; $steps++) {
            $value = $this->object($value->number);
        }

        return $value instanceof Reference ? null : $value;
    }

    /**
     * Adds an object and returns the reference to it.
     */
    public function add(mixed $value): Reference
    {
        $number = -++$this->added;
        $this->objects[$number] = $value;

        return new Reference($number);
    }
}
