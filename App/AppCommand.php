<?php

namespace App;

use App\Exception\MessageAlreadySentException;
use App\Provider\Provider;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppCommand extends Command
{
    /**
     * Configuration of the Application
     * @var array
     */
    private $config;

    /**
     * Message sender
     * @var MessageService
     */
    private $message;

    protected static $defaultName = 'app:run';

    /**
     * Array of providers
     * @var array of Provider
     */
    protected $providers;

    /**
     * Constructor
     * @param array $config config of the Application
     */
    public function __construct(array $config, MessageService $message, array $providers)
    {

        $this->config    = $config;
        $this->message   = $message;
        $this->providers = $providers;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Poll the balticgruppen API and send an SMS.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $section1 = $output->section();
        $section2 = $output->section();
        $section3 = $output->section();

        $statisticsTable = new Table($section2);
        $statisticsTable
            ->setHeaderTitle('Statistics')
            ->setHeaders(['Last time fetched', 'Errors', 'Success', 'Apartement availables'])
            ->setStyle('box')
            ->setRows([
                ['...', 0, 0, 0],
            ]);
        $statisticsTable->render();

        // Start an infinite loop, wait 5 seconds betwwen each execution and output "Sleep..." on section 3
        (new Loop($section3))->setSecondsToWait(5)
            ->runAndWait(function () use ($section1, $section2, $statisticsTable) {

                // Build table rows;
                $tableRows = [];

                foreach ($this->providers as $provider) {
                    try {
                        $result = $provider->fetch();

                        // If some residence are available
                        if ($result->hasAvailable()) {
                            $section1->writeln("<info>AVAILABLE</info>");

                            foreach ($result->value as $key => $object) {

                                $section1->writeln('Price: ' . $object->getCost());
                                var_dump($result);

                                try {
                                    $section1->writeln("<info>" . $provider->disponibilityStringGenerator($object) . "</info>");

                                    // Warn
                                    $provider->disponibilityHandler($object);

                                } catch (MessageAlreadySentException $e) {
                                    $section1->writeln('<comment>Message already sent (id: ' . $object->getId() . ')</comment>');

                                }

                            }

                        }
                    } catch (TransferException $e) {
                        $section1->writeln('<error>URL could not be fetched</error>');
                        $section1->writeln('<error>' . $e->getMessage() . '</error>');
                        $provider->addError();

                    } catch (\Exception $e) {
                        $section1->writeln('<error>' . $e->getMessage() . '</error>');
                        $provider->addError();
                    }

                    $tableRows[] = [$provider->getName() . ' ' . $provider->lastTimeFetched . ' status: ' . $provider->statistics['lastStatus'],
                            $provider->statistics['errors'],
                            $provider->statistics['success'],
                            $provider->available,
                        ];

                }

                //Clear the section 2 to rhe table.
                $section2->clear();

                $statisticsTable
                    ->setRows($tableRows);
                $statisticsTable->render();

            });

    }

}
