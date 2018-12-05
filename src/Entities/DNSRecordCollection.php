<?php
namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Serializable;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;

class DNSRecordCollection extends EntityAbstract implements \ArrayAccess, \Iterator, \Countable, Arrayable, Serializable
{
    /**
     * @var \ArrayIterator|\RemotelyLiving\PHPDNS\Entities\DNSRecord[]
     */
    private $records;

    public function __construct(DNSRecord ...$records)
    {
        $this->records = new \ArrayIterator($records);
    }

    public function toArray(): array
    {
        return $this->records->getArrayCopy();
    }

    public function pickFirst(): ?DNSRecord
    {
        $copy = $this->records->getArrayCopy();

        return array_shift($copy);
    }

    public function filteredByType(DNSRecordType $type): self
    {
        return new self(...array_filter($this->records->getArrayCopy(), function (DNSRecord $record) use ($type) {
            return $record->getType()->equals($type);
        }));
    }

    public function has(DNSRecord $lookupRecord): bool
    {
        foreach ($this->records->getArrayCopy() as $record) {
            if ($lookupRecord->equals($record)) {
                return true;
            }
        }

        return false;
    }

    public function current(): ?DNSRecord
    {
        return $this->records->current();
    }

    public function next(): void
    {
        $this->records->next();
    }

    /**
     * @return int|string|bool
     */
    public function key()
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

    public function offsetExists($offset): bool
    {
        return $this->records->offsetExists($offset);
    }

    public function offsetGet($offset): DNSRecord
    {
        return $this->records->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof DNSRecord) {
            throw new InvalidArgumentException('Invalid value');
        }

        $this->records->offsetSet($offset, /** @scrutinizer ignore-type */ $value);
    }

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

    public function serialize(): string
    {
        return \serialize($this->records->getArrayCopy());
    }

    public function unserialize($records): void
    {
        $this->records = new \ArrayIterator(\unserialize($records));
    }

    public function withUniqueValuesExcluded(): self
    {
        return $this->filterValues(function (DNSRecord $candidateRecord, DNSRecordCollection $remaining): bool {
            return $remaining->has($candidateRecord);
        })->withUniqueValues();
    }

    public function withUniqueValues(): self
    {
        return $this->filterValues(function (DNSRecord $candidateRecord, DNSRecordCollection $remaining): bool {
            return !$remaining->has($candidateRecord);
        });
    }

    private function filterValues(callable $eval): self
    {
        $filtered = new self();
        $records = $this->records->getArrayCopy();

        /** @var \RemotelyLiving\PHPDNS\Entities\DNSRecord $record */
        while ($record = array_shift($records)) {
            if ($eval($record, new self(...$records))) {
                $filtered[] = $record;
            }
        }

        return $filtered;
    }
}
