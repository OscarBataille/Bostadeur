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

    private $secondsToWait = 0;

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

            $this->output->overwrite('Sleep...');

            sleep($this->secondsToWait);
            $this->output->clear();
        }
    }
}
