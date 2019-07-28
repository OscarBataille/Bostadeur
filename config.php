<?php

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
    'domain'    => 'https://u4pp.u4a.se/FN667500P/',
    'twilio'    => [
        'sid'   => 'AC69df257d00521bee4c1439a7854ea4fe',
        'token' => '61e22785fe2845664d75b619f3ce66d3',
        'from'  => '+46790645167',
        'to'    => '+46793360007',
    ],
];
