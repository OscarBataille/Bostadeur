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

    public function getAvailableEntries(): ProviderResult
    {
        $data = $this->fetchAvailableResidence();

        $output = new ProviderResult();

        $output->status = $data['status'];
        $output->count = $data['count'];
        $output->value = array_map(function($value) {
            return new BalticgruppenEntry($value);
        }, $data['data']);


        return $output;




    }

}
