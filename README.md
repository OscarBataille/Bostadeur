# UmeaBostad
Aggregate available appartments of private owners in Umeå and notify by SMS with the Twilio API.


# Usage
1. Download the source
2. ...
 
# Add a provider/ residence owner
1. Create a class that extends App\Provider (like BalticgruppenProvider or DiosProvider). That class needs to implement the method getAvailableEntries().
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




