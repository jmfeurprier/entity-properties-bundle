# Entity rendering bundle

Symfony bundle which allows rendering entities in Twig templates.

## Installation & Requirements

Install with [Composer](https://getcomposer.org):

```shell script
composer require jmf/entity-rendering-bundle
```

If you have [Flex](https://symfony.com/packages/Symfony%20Flex) installed, the bundle is then instantly available without any need for initial configuration.
If not, complete your `config/bundles.php` file as indicated below:

```php
<?php

return [
    // ...
    // Other existing bundles.
    // ...
    Jmf\EntityRendering\EntityRenderingBundle::class => ['all' => true],
];
```
