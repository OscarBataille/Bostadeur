<?php

return [
    'name'      => 'Bostadeur',
    'version'   => '1.1',
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
    'actions'   => [
        \App\Action\OpenFirefoxAction::class => [
            'binary' => '/opt/firefox/firefox-bin',
        ],
        // Comment that part if you do not need TWILIO SMS
        \App\Action\SMSAction::class         => [
            // Config in .env
        ],
        \App\Action\SoundAction::class       => [
            'text' => 'APARTEMENT AVAILABLE',
        ],

    ],

];
