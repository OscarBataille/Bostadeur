<?php
use Dotenv\Exception\ValidationException;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
try{
$dotenv->required(['TWILIO_TOKEN', 'TWILIO_SID', 'TWILIO_TO', 'TWILIO_FROM']);

}catch(ValidationException $e){
    throw new Exception('The twilio configuration is missing in the .env file.');    
}
return [
    'providers' => [
        \App\Provider\BalticgruppenProvider::class => [
            'factory'     => \App\ProviderFactory\BalticgruppenFactory::class,
            'domain'      => 'https://u4pp.u4a.se/FN667500P/',
            'apiEndpoint' => 'odata/tenant/PublishEntries?$expand=LeaseOutCase($expand=Address,MainImage,Details)&$orderby=LeaseOutCase/Address/StreetAddress&$count=true&$filter=(ContractType%20eq%20TenantModels.ContractType%27Residence%27)',
        ],
        \App\Provider\DiosProvider::class          => [
            'factory'     => \App\ProviderFactory\DiosFactory::class,
            'domain'      => 'https://www.dios.se/',
            'apiEndpoint' => 'api/bostad',
        ],
    ],

];
