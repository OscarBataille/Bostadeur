<?php

namespace App;

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

    /**
     * Array of all the object ids that are already warned.
     * @var array
     */
    private $messageSents = [];

    protected static $defaultName = 'app:run';

    protected $statistics = [
        'errors'     => 0,
        'success'    => 0,
        'lastStatus' => null,
    ];

    protected $available = 0;

    protected $lastTimeFetched;

    /**
     * Constructor
     * @param array $config config of the Application
     */
    public function __construct(array $config, MessageService $message, APIService $apiService)
    {

        $this->config     = $config;
        $this->message    = $message;
        $this->apiService = $apiService;

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
                [$this->lastTimeFetched, $this->statistics['errors'], $this->statistics['success'], $this->available],
            ]);
        $statisticsTable->render();

        $loop = new Loop($section3);

        $loop->setSecondsToWait(5)
            ->runAndWait(function () use ($section1, $section2, $statisticsTable) {

                $this->lastTimeFetched = date('H:i:s');
                $this->statistics['lastStatus'] = '...';
                //$result                = file_get_contents($this->config['domain'] . 'odata/tenant/PublishEntries?$expand=LeaseOutCase($expand=Address,MainImage,Details)&$orderby=LeaseOutCase/Address/StreetAddress&$count=true&$filter=(ContractType%20eq%20TenantModels.ContractType%27Residence%27)');

                try {

                    $result = $this->apiService->fetchAvailableResidence();

                    $this->statistics['success']++;
                    $this->available                = $result['count'];
                    $this->statistics['lastStatus'] = $result['status'];

                    if (!empty($result['data'])) {
                        $section1->writeln("<info>AVAILABLE !! </info>");

                        foreach ($result['data'] as $key => $objectData) {

                            $object = new PublishEntry($objectData);

                            $section1->writeln('Price: ' . $object->getCost());
                            var_dump($result);

                            if (!in_array($object->getId(), $this->messageSents)) {

                                // Say it
                                shell_exec("spd-say 'APARTMENT AVAILABLE' ");

                                // Send sms
                                $section1->writeln('<info>APPARTEMENT dispo ' . $object->getId() . ',  price: ' . $object->getCost() . 'kr., Address: ' . $object->getAddress() . ' ' . $this->config['domain'] . "tenant/dashboard </info>");
                                $this->message->send('APPARTEMENT dispo ' . $object->getId() . ',  price: ' . $object->getCost() . 'kr., Address: ' . $object->getAddress() . ' ' . $this->config['domain'] . "tenant/dashboard");

                                // Open firefox
                                shell_exec("/opt/firefox/firefox-bin " . $this->config['domain'] . "tenant/dashboard");

                                $this->messageSents[] = $object->getId();
                            } else {
                                $section1->writeln('<comment>Message already sent</comment>');
                            }

                        }

                    }
                } catch (TransferException $e) {
                    $section1->writeln('<error>URL could not be fetched</error>');
                    $section1->writeln('<error>' . $e->getMessage() . '</error>');
                    $this->statistics['errors']++;

                } catch (\Exception $e) {
                    $section1->writeln('<error>' . $e->getMessage() . '</error>');
                    $this->statistics['errors']++;

                }

                //Clear the section 2 to rhe table.
                $section2->clear();

                $statisticsTable
                    ->setRows([
                        [$this->lastTimeFetched . ' status: ' . $this->statistics['lastStatus'], $this->statistics['errors'], $this->statistics['success'], $this->available],
                    ]);
                $statisticsTable->render();

            });

    }

}
