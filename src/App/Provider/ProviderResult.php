<?php

namespace App\Provider;

use App\Entry\EntryInterface;

/**
 * Represent the result of Provider::getAvailableEntries()
 */
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

    /**
     * Set the HTTP status of the result
     * @param int $status
     */
    public function setStatus(int $status): self
    {

        $this->status = $status;

        return $this;
    }

    /**
     * Set the number of available objects
     * @param int $count
     */
    public function setCount(int $count): self
    {

        $this->count = $count;

        return $this;
    }

    /**
     * Set the value
     * @param array $data Array of EntryInterface
     */
    public function setValue(array $data): self
    {
        $this->value = (function (EntryInterface...$entry) {
            return $entry;
        })(...$data);

        return $this;

    }

    /**
     * Return true if there is some available appartments.
     * @return boolean
     */
    public function hasAvailable(): bool
    {
        return !empty($this->value);
    }
}
