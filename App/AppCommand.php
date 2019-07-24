<?php

namespace App;

use Symfony\Component\Console\Command\Command;
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
     * @var Message
     */
    private $message;

    /**
     * Array of all the object ids.
     * @var array
     */
    private $messageSents = [1345];

    protected static $defaultName = 'app:run';

    protected $statistics = [
        'errors' => 0,
        'success' => 0
    ];

    protected $available = 0;

    /**
     * Constructor
     * @param array $config config of the Application
     */
    public function __construct(array $config, MessageService $message)
    {

        $this->config  = $config;
        $this->message = $message;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Poll the balticgruppen API and send an SMS.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (true) {

             $section1 = $output->section();
            $section2 = $output->section();

           $section1->writeln('Start ' . date('H:i:s'));
            $result = file_get_contents($this->config['domain'] . 'odata/tenant/PublishEntries?$expand=LeaseOutCase($expand=Address,MainImage,Details)&$orderby=LeaseOutCase/Address/StreetAddress&$count=true&$filter=(ContractType%20eq%20TenantModels.ContractType%27Residence%27)');

            if (!$result) {
               $section1->writeln('<error>URL could not be fetched</error>');
                $this->statistics['errors']++;

            } else {
                $this->statistics['success']++;

                $json = json_decode($result, JSON_OBJECT_AS_ARRAY);
                if (!empty($json['value'])) {
                   $section1->writeln("\033[31m AVAILABLE !! \033[0m");

                    foreach ($json['value'] as $key => $objectData) {

                        $object = new PublishEntry($objectData);

                       $section1->writeln('Price: ' . $object->getCost());
                        var_dump($json);

                        if (!in_array($object->getId(), $this->messageSents)) {

                            // Say it
                            shell_exec("spd-say 'APARTMENT AVAILABLE' ");

                            // Send sms
                           $section1->writeln('<info>APPARTEMENT dispo ' . $object->getId() . ',  price: ' . $object->getCost() . 'kr., Address: ' . $object->getAddress() . ' ' . $this->config['domain'] . "tenant/dashboard </info>");
                            $this->message->send('APPARTEMENT dispo ' . $object->getId() . ',  price: ' . $object->getCost() . 'kr., Address: ' . $object->getAddress() . ' ' . $this->config['domain'] . "tenant/dashboard");

                            // Open firefoxss
                            shell_exec("/opt/firefox/firefox-bin " . $this->config['domain'] . "tenant/dashboard");

                            $this->messageSents[] = $object->getId();
                        } else {
                           $section1->writeln('<comment>Message already sent</comment>');
                        }

                    }

                } else {
                   $section1->writeln("Not available");

                }
            }

            $section2->overwrite('Statistics: <error>errors:'.$this->statistics['errors'].'</error>'.' <info>success:'.$this->statistics['success'].'</info>');

            sleep(20 + rand(1, 20));
        }

    }

}
