<?php
declare(strict_types=1);

namespace App\Provider;

use App\Action\ActionExecutor;
use App\Entry\DiosEntry;
use GuzzleHttp\Client as HTTPClient;

class DiosProvider extends Provider
{

    /**
     * Http client
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Endpoint of the API.
     *
     * @var string
     */
    private $endpoint;

    /**
     * API domain
     *
     * @var string
     */
    private $url;

    public function __construct(HTTPClient $client, ActionExecutor $actionExecutor, string $domain, string $url)
    {
        $this->client = $client;
        $this->domain = $domain;
        $this->url    = $url;

        parent::__construct($actionExecutor);
    }

    public function getAvailableEntries(): ProviderResult
    {
        $response = $this->client->request('GET', $this->domain . $this->url);

        $status = $response->getStatusCode(); // 200

        $body = (string) $response->getBody();

        $json = json_decode($body, true);

        // Filter the city here
        $data = array_filter(
            $json,
            function ($entry) {
                return preg_match('/ume(å|Å)/i', $entry['city']);
            }
        );

        return (new ProviderResult())
            ->setStatus($status)
            ->setCount(count($data))
            ->setValue(
                array_map(
                    function ($value) {
                        return new DiosEntry($value);
                    },
                    $data
                )
            );
    }
}
