<?php

namespace App\Provider;

class ProviderResult
{

    /**
     * Request status
     * @var intl
     */
    public $status;

    /**
     * Count of results
     * @var integer
     */
    public $count = 0;

    /**
     * Array of EntryInterface
     * @var array
     */
    public $value;

    public function setStatus(int $status): self
    {

        $this->status = $status;

        return $this;
    }

    public function setCount(int $count): self
    {

        $this->count = $count;

        return $this;
    }

    public function setValue(array $data): self
    {
        $this->value = (function (EntryInterface ...$entry) {
            return $entry;
        })(...$data);

        return $this;

    }

    public function hasAvailable(): bool{
        return !empty($this->value);
    }
}
