<?php
declare(strict_types=1);

namespace App\Provider;

use App\Action\ActionExecutor;
use App\Entry\EntryInterface;
use App\Exception\MessageAlreadySentException;

/**
 * Abstract class Provider that must be extended and must implement getAvailableEntries().
 * this allow different datasources.
 */
abstract class Provider
{

    public $statistics = [
        'errors'     => 0,
        'success'    => 0,
        'lastStatus' => null,
    ];

    /**
     * The last date when we sent the last query
     *
     * @var string
     */
    public $lastTimeFetched;

    /**
     * Array of all the object ids that are already warned.
     *
     * @var array
     */
    private $messageSents = [];

    /**
     * @var ActionExecutor
     */
    protected $actionExecutor;

    abstract public function getAvailableEntries(): ProviderResult;

    public function __construct(ActionExecutor $actionExecutor)
    {
        $this->actionExecutor = $actionExecutor;
    }
    /**
     * Wrap getAvailableEntries to get the statistics
     *
     * @return ProviderResult
     */
    final public function fetch(): ProviderResult
    {
        $this->lastTimeFetched          = date('H:i:s');
        $this->statistics['lastStatus'] = '...';

        $result = $this->getAvailableEntries();

        $this->statistics['success']++;
        $this->available                = $result->count;
        $this->statistics['lastStatus'] = $result->status;

        return $result;
    }

    public function getName()
    {
        $reflection = new \ReflectionClass($this);

        return preg_replace('~Provider$~', '', $reflection->getShortName());
    }

    public function addError()
    {
        $this->statistics['errors']++;
    }
    /**
     * Run when an appartment is available.
     *
     * @param  EntryInterface $object      The available object.
     * @param bool $sendMessage Flag to warn or not.
     * @return void
     * @throws MessageAlreadySentException
     */
    public function disponibilityHandler(EntryInterface $object, bool $sendMessage = true): void
    {

        if (!in_array($object->getId(), $this->messageSents)) {
            if ($sendMessage) {
                $this->actionExecutor->run($object, $this);
            }

            $this->messageSents[] = $object->getId();
        } else {
            throw new MessageAlreadySentException();
        }
    }

    /**
     * Generate the string that will be set by SMS and logged into the console.
     *
     * @param  EntryInterface $object Appartement entry.
     * @return string
     */
    public function disponibilityStringGenerator(EntryInterface $object): string
    {
        $string = <<<ENDSTRING

APARTEMENT available: {$this->getName()} {$object->getId()},
Price:   {$object->getCost()} kr.
Address: {$object->getAddress()}
Url: {$object->getUrl()}
ENDSTRING;

        return $string;
    }
}
