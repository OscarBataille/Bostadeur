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

            $output->writeln('Start ' . date('H:i:s'));
            $result = file_get_contents($this->config['domain'] . 'odata/tenant/PublishEntries?$expand=LeaseOutCase($expand=Address,MainImage,Details)&$orderby=LeaseOutCase/Address/StreetAddress&$count=true&$filter=(ContractType%20eq%20TenantModels.ContractType%27Residence%27)');

            if (!$result) {
                $output->writeln('<error>URL could not be fetched</error>');
                var_dump($result);
            } else {
                $json = json_decode($result, JSON_OBJECT_AS_ARRAY);
                if (!empty($json['value'])) {
                    $output->writeln("\033[31m AVAILABLE !! \033[0m");

                    foreach ($json['value'] as $key => $objectData) {

                        $object = new PublishEntry($objectData);

                        $output->writeln('Price: ' . $object->getCost());
                        var_dump($json);

                        if (!in_array($object->getId(), $this->messageSents)) {

                            // Say it
                            shell_exec("spd-say 'APARTMENT AVAILABLE' ");

                            // Send sms
                            $output->writeln('<info>APPARTEMENT dispo ' . $object->getId() . ',  price: ' . $object->getCost() . 'kr., Address: ' . $object->getAddress() . ' ' . $this->config['domain'] . "tenant/dashboard </info>");
                            $this->message->send('APPARTEMENT dispo ' . $object->getId() . ',  price: ' . $object->getCost() . 'kr., Address: ' . $object->getAddress() . ' ' . $this->config['domain'] . "tenant/dashboard");

                            // Open firefoxss
                            shell_exec("/opt/firefox/firefox-bin " . $this->config['domain'] . "tenant/dashboard");

                            $this->messageSents[] = $object->getId();
                        } else {
                            $output->writeln('<comment>Message already sent</comment>');
                        }

                    }

                } else {
                    $output->writeln("Not available");

                }
            }

            sleep(20 + rand(1, 20));
        }

    }

}
