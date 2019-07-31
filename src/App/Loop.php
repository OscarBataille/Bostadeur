<?php

namespace App;

use Symfony\Component\Console\Output\OutputInterface;

class Loop
{

    /**
     * Output
     * @var OutputInterface
     */
    private $output;

    /**
     * Seconds to wait betwwen each loop execution.
     * @var integer
     */
    private $secondsToWait = 0;

    /**
     * Is first loop execution
     * @var boolean
     */
    private $isFirstExecution = true;

    /**
     * Get the ection to output Sleep...
     * @param OutputInterface $output COnsole output section.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setSecondsToWait(int $seconds)
    {
        $this->secondsToWait = $seconds;

        return $this;
    }

    public function runAndWait(callable $function)
    {

        while (true) {

            $function();

            $this->setRunOnce();

            $this->output->overwrite('Sleep...');

            sleep($this->secondsToWait);
            $this->output->clear();
        }
    }

    public function setRunOnce(){
        $this->isFirstExecution = false;
    }

    public function hasRunOnce(): bool{
        return !$this->isFirstExecution;
    }
}
