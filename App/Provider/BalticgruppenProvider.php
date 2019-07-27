<?php

namespace App\Provider;

use \GuzzleHttp\Client as HTTPClient;

class BalticgruppenProvider extends Provider
{

    /**
     * Http client
     * @var GuzzleHttp\Client
     */
    private $client;

    public function __construct(HTTPClient $client)
    {
        $this->client = $client;
    }

    public function fetchAvailableResidence(): array
    {

        $response = $this->client->request('GET', 'odata/tenant/PublishEntries?$expand=LeaseOutCase($expand=Address,MainImage,Details)&$orderby=LeaseOutCase/Address/StreetAddress&$count=true&$filter=(ContractType%20eq%20TenantModels.ContractType%27Residence%27)');

        $status = $response->getStatusCode(); // 200

        $body = (string) $response->getBody();

        $json = json_decode($body, JSON_OBJECT_AS_ARRAY);

        return [
            'status' => $status,
            'count'  => $json['@odata.count'],
            'data'   => $json['value'],
        ];
    }

    /**
     * Fetch the data and return a ProviderResult object
     * @return ProviderResult
     */
    public function getAvailableEntries(): ProviderResult
    {
        $data = $this->fetchAvailableResidence();

        return (new ProviderResult())
            ->setStatus($data['status'])
            ->setCount($data['count'])
            ->setValue(array_map(function ($value) {
                return new BalticgruppenEntry($value);
            }, $data['data']));

    }

}
