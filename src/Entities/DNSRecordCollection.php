<?php

namespace RemotelyLiving\PHPDNS\Entities;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Serializable;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;

use function array_filter;
use function array_shift;

final class DNSRecordCollection extends EntityAbstract implements
    ArrayAccess,
    Iterator,
    Countable,
    Arrayable,
    Serializable
{
    private ArrayIterator $records;

    public function __construct(DNSRecordInterface ...$records)
    {
        $this->records = new ArrayIterator($records);
    }

    public function toArray(): array
    {
        return $this->records->getArrayCopy();
    }

    public function pickFirst(): ?DNSRecordInterface
    {
        $copy = $this->records->getArrayCopy();

        return array_shift($copy);
    }

    public function filteredByType(DNSRecordType $type): self
    {
        $fn = fn(DNSRecordInterface $record) => $record->getType()->equals($type);
        return new self(...array_filter($this->records->getArrayCopy(), $fn));
    }

    public function has(DNSRecordInterface $lookupRecord): bool
    {
        foreach ($this->records->getArrayCopy() as $record) {
            if ($lookupRecord->equals($record)) {
                return true;
            }
        }

        return false;
    }

    public function current(): ?DNSRecordInterface
    {
        return $this->records->current();
    }

    public function next(): void
    {
        $this->records->next();
    }

    public function key(): bool|int|string|null
    {
        return $this->records->key();
    }

    public function valid(): bool
    {
        return $this->records->valid();
    }

    public function rewind(): void
    {
        $this->records->rewind();
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->records->offsetExists($offset);
    }

    /**
     * @param mixed $offset
     */
    public function offsetGet($offset): DNSRecordInterface
    {
        return $this->records->offsetGet($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof DNSRecordInterface) {
            throw new InvalidArgumentException('Invalid value');
        }

        $this->records->offsetSet($offset, /** @scrutinizer ignore-type */ $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        $this->records->offsetUnset($offset);
    }

    public function count(): int
    {
        return $this->records->count();
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function __serialize(): array
    {
        return $this->records->getArrayCopy();
    }

    public function __unserialize(array $unserialized): void
    {
        $this->records = new ArrayIterator($unserialized);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function withUniqueValuesExcluded(): self
    {
        return $this->filterValues(
            fn(DNSRecordInterface $candidateRecord, DNSRecordCollection $remaining): bool => $remaining->has(
                $candidateRecord
            )
        )->withUniqueValues();
    }

    public function withUniqueValues(): self
    {
        return $this->filterValues(
            fn(DNSRecordInterface $candidateRecord, DNSRecordCollection $remaining): bool => !$remaining->has(
                $candidateRecord
            )
        );
    }

    private function filterValues(callable $eval): self
    {
        $filtered = new self();
        $records = $this->records->getArrayCopy();

        while ($record = array_shift($records)) {
            if ($eval($record, new self(...$records))) {
                $filtered[] = $record;
            }
        }

        return $filtered;
    }
}
