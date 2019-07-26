<?php

namespace App;

use App\Exception\MessageAlreadySentException;
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

    /**
     * Number of available apartments
     * @var integer
     */
    protected $available = 0;

    /**
     * The last date when we sent the last query
     * @var string
     */
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

        // Start an infinite loop, wait 5 seconds betwwen each execution and output "Sleep..." on section 3
        (new Loop($section3))->setSecondsToWait(5)
            ->runAndWait(function () use ($section1, $section2, $statisticsTable) {

                $this->lastTimeFetched          = date('H:i:s');
                $this->statistics['lastStatus'] = '...';

                try {

                    $result = $this->apiService->fetchAvailableResidence();

                    $this->statistics['success']++;
                    $this->available                = $result['count'];
                    $this->statistics['lastStatus'] = $result['status'];

                    // If some residence are available
                    if (!empty($result['data'])) {
                        $section1->writeln("<info>AVAILABLE !! </info>");

                        foreach ($result['data'] as $key => $objectData) {

                            $object = new PublishEntry($objectData);

                            $section1->writeln('Price: ' . $object->getCost());
                            var_dump($result);

                            // Warn
                            try {
                                $section1->writeln("<info>" . $this->disponibilityStringGenerator($object) . "</info>");

                                $this->disponibilityHandler($object);

                            } catch (MessageAlreadySentException $e) {
                                $section1->writeln('<comment>Message already sent (id: ' . $object->getId() . ')</comment>');

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

    /**
     * Run when an appartment is available.
     * @param  PublishEntry    $object The available object.
     * @return void
     * @throws MessageAlreadySentException
     */
    public function disponibilityHandler(PublishEntry $object): void
    {

        if (!in_array($object->getId(), $this->messageSents)) {

            // // Say it
            // shell_exec("spd-say 'APARTMENT AVAILABLE'");

            // // Send sms
            // $this->message->send($this->disponibilityStringGenerator($object));

            // // Open firefox
            // shell_exec("/opt/firefox/firefox-bin " . $this->config['domain'] . "tenant/dashboard");

            $this->messageSents[] = $object->getId();
        } else {
            throw new MessageAlreadySentException();
        }

    }

    /**
     * Generate the string that will be set by SMS and logged into the console.
     * @param  PublishEntry $object Appartement entry.
     * @return string
     */
    public function disponibilityStringGenerator(PublishEntry $object): string
    {
        $string =  <<<ENDSTRING
APPARTEMENT dispo:   {$object->getId()},  
Price:   {$object->getCost()} kr. 
Address: {$object->getAddress()}  
{$this->config['domain']}tenant/dashboard
ENDSTRING;

        return $string;
    }

}
