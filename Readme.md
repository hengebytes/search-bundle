## Installation
```bash
composer require aternovtsii/search-bundle
```

#### To enable bundle add the following to your config/bundles.php file
```php
<?php

return [
    // ...
    ATSearchBundle\ATSearchBundle::class => ['all' => true],
];
```
#### To enable additional doctrine filters add the following to your config/doctrine.yaml file
```yaml
doctrine:
    orm:
        dql:
            numeric_functions:
                IFNULL: ATSearchBundle\Doctrine\Extensions\Query\IfNull
                JSON_CONTAINS: ATSearchBundle\Doctrine\Extensions\Query\JsonContains
                RAND: ATSearchBundle\Doctrine\Extensions\Query\Rand

```
#### Configure bundle in your config/packages/at_search.yaml file
```yaml
at_search:
    search:
        enabled: true
        mappings:
            App:
                namespace: App\Entity
                dir: '%kernel.project_dir%/src/Entity'


```

#### To enable OpenSearch Client add the following to your config/services.yaml file

```yaml

    OpenSearch\Client:
        factory: ['OpenSearch\ClientBuilder', 'fromConfig']
        arguments:
            $config:
                hosts: ['%env(OPENSEARCH_URL)%']
```

#### ~~To enable ElasticSearch Client add the following to your config/services.yaml file~~ NOT SUPPORTED YET
```yaml

    Elasticsearch\Client:
        factory: ['Elasticsearch\ClientBuilder', 'fromConfig']
        arguments:
            $config:
                hosts: ['%env(ELASTICSEARCH_URL)%']
```

#### Usage example

```php
<?php

namespace App\Entity;

use ATSearchBundle\Annotation as ATSearch;
use Doctrine\ORM\Mapping as ORM;

#[ATSearch\Index]
#[ORM\Entity]
class User
{
    #[ATSearch\Id]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ATSearch\String]
    #[ORM\Column(length: 128)]
    public ?string $firstName = null;

    #[ATSearch\FieldMultiString(subFields: 'email')]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Email::class)]
    public Collection $emails;
```

#### To enable default reindex based on Doctrine events add the following to your config/packages/at_search.yaml file

```yaml
at_search:
    search:
        enable_update_events: true # default false
```