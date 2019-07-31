# UmeaBostad
Aggregate available appartments of private owners in Umeå and notify by SMS with the Twilio API.


# Usage
1. Download the source
2. ...
 
# Add a provider/ residence owner
1. Create a class that extends the abstract class App\Provider\Provider (like BalticgruppenProvider or DiosProvider). That class needs to implement the method getAvailableEntries(). That method will be called on each loop execution.
```php
...
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
```
### EntryInterface
The method ```getAvailableEntries()``` must return an instance of ```ProviderResult``` which contains an array of ```App\Entry\EntryInterface```, so you will also need to create a class that implements ```EntryInterface``` ( in ```App\Entry``` for example).
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

2. Create a factory for that provider in App\ProviderFactory that exentends AbstractProviderFactory and implement the method make()
3. Add the config in config.php under 'providers': 
```php
  \App\Provider\DiosProvider::class          => [
            'factory'     => \App\ProviderFactory\DiosFactory::class,
            'domain'      => 'https://www.dios.se/',
            'apiEndpoint' => 'api/bostad',
        ],
```
![Image](/static/carbon.png)




