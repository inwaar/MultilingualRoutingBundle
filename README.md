MultilingualRoutingBundle [![Build Status](https://travis-ci.org/inwaar/MultilingualRoutingBundle.svg?branch=master)](https://travis-ci.org/inwaar/MultilingualRoutingBundle)
======

Multilingual Routing Bundle for the Symfony Framework


Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require inwaar/multilingual-routing-bundle:dev-master
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new MultilingualRoutingBundle\MultilingualRoutingBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundle
-------------------------
```yaml
# app/config/config.yml
 
multilingual_routing:
    map:
      en: com
      en_GB: co.uk
```

_The map is optional._


Add `type: localized` to the route definition, so the root route (/) will be appended by {_locale}.
```yaml
# app/config/routing.yml
app:
      resource: "@AppBundle/Resources/config/routing.yml"
      prefix: /
      type: localized
```
Or
```yaml
# app/config/config.yml
framework:
    router:
        type: localized
        resource: '%kernel.root_dir%/routing.yml'
```

Or configure the route manually.

Usage
============

Make sure all domains point to the host you run the app.
Or add a list to your local host config.
 
```text
# /etc/host

127.0.0.1 example.com
127.0.0.1 example.nl
127.0.0.1 example.fr
127.0.0.1 example.us
127.0.0.1 example.co.uk
```

Example application
-------------------------
https://github.com/inwaar/multilingual-routing-example
