# Bostadeur
PHP CLI Framework to aggregate available appartments from different sources and notify by SMS with the Twilio API.
It is based on [symfony/console](https://github.com/symfony/console). It also automatically open firefox at the correct URL and shout 'APARTMENT AVAILABLE' with spd-say.

![Image](/static/carbon.png)

# Usage
1. Download the source: ```git clone https://github.com/OscarBataille/Bostadeur```
2. Move to the downloaded directory: ```cd Bostadeur/src```
3. Copy ```.env.example``` to ```.env```: ```cp .env.example .env```
4. Edit  ```.env``` to add your twilio config. 
5. Install the required composer packages: ```composer install```
6. Run with ```php index.php``` in the 'src' folder.

## Options
- ```--dry-run``` : Does not send an SMS.
- ```--seconds-to-wait=5``` : Number of seconds to wait between each loop execution. Default to 5.

# Requirements
- PHP >= 7.2.19
- Composer
- ```spd-say``` binary to shout 'APARTMENT AVAILABLE'
- ```/opt/firefox/firefox-bin``` to open firefox at the good page

# Add a provider/ residence owner
### Provider
1. Create a class that extends the abstract class ```App\Provider\Provider``` (like BalticgruppenProvider or DiosProvider). That class needs to implement the method ```getAvailableEntries()```. That method will be called on each loop execution.
```php
...
<?php

namespace App\Provider;

use App\Entry\DiosEntry;
use App\MessageService;
use GuzzleHttp\Client as HTTPClient;

class DiosProvider extends Provider
{

    /** Http client
     * @var GuzzleHttp\Client
     */
    private $client;

    public function __construct(HTTPClient $client, MessageService $message, string $domain, string $url)
    {
        $this->client = $client;
        $this->domain = $domain;
        $this->url    = $url;

        parent::__construct($message);
    }

    public function getAvailableEntries(): ProviderResult
    {
        $response = $this->client->request('GET', $this->domain . $this->url);

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

```
### ProviderResult and EntryInterface
The method ```getAvailableEntries()``` must return an instance of ```App\Provider\ProviderResult``` which contains an array of ```App\Entry\EntryInterface``` (in the $value property) , so you will also need to create a class that implements ```EntryInterface``` to represents each residence object. The method getId() of that class must return an unique id that will be used to keep track of the already sent SMS.
```php
<?php

namespace App\Entry;

class DiosEntry implements EntryInterface
{

    protected $data;

    public function __construct(array $data)
    {

        $this->data = $data;
    }

    public function getId(): int
    {
        return $this->data['id'];
    }

    public function getAddress(): string
    {
        return $this->data['name'];
    }

    public function getCost(): int
    {
        return (int) $this->data['rent'];
    }

    public function getUrl(): string
    {
        return 'https://www.dios.se' . $this->data['url'];
    }
}

```
### Provider factory
2. Create a factory for that provider in ```App\ProviderFactory``` that exentends ```AbstractProviderFactory``` and implement the abstract method ```make()``` (which return an instance of the provider).
```php
<?php
namespace App\ProviderFactory;

use App\Provider\DiosProvider;
use App\Provider\Provider;
use App\MessageService;

class DiosFactory extends AbstractProviderFactory
{

    public function make(array $config): Provider
    {
        $providerConfig = $config[DiosProvider::class];
        return new DiosProvider($this->container->get(\GuzzleHttp\Client::class),$this->container->get(MessageService::class), $providerConfig['domain'],  $providerConfig['apiEndpoint']);
        


    }
}

```
### Config.php

3. Add the config in ```config.php``` under 'providers': 
```php
  \App\Provider\DiosProvider::class          => [
            'factory'     => \App\ProviderFactory\DiosFactory::class,
            'domain'      => 'https://www.dios.se/',
            'apiEndpoint' => 'api/bostad',
        ],
```




