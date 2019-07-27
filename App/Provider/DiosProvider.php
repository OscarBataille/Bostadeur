<?php

namespace App\Provider;

use \GuzzleHttp\Client as HTTPClient;

class DiosProvider extends Provider
{

    /** Http client
     * @var GuzzleHttp\Client
     */
    private $client;

    public function __construct(HTTPClient $client)
    {
        $this->client = $client;
    }

    public function getAvailableEntries(): ProviderResult
    {
        $response = $this->client->request('GET', 'https://www.dios.se/api/bostad');

        $status = $response->getStatusCode(); // 200

        $body = (string) $response->getBody();

        $json = json_decode($body, JSON_OBJECT_AS_ARRAY);

        // Filter the city here
        $data = array_filter($json, function ($entry) {
            return preg_match('/ume(å|Å)/i', $entry['city']);
        });

        return (new ProviderResult())
            ->setStatus($status)
            ->setCount(count($data))
            ->setValue(array_map(function ($value) {
                return new DiosEntry($value);
            }, $data));

    }

}