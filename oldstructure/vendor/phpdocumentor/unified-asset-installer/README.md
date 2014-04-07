Unified Asset Installer
=======================

phpDocumentor relies on specific directory locations for templates and plugins.
By default [Composer](http://getcomposer.org) is unable to install in an other
directory than /vendor except when using a
[Custom Installer](http://getcomposer.org/doc/articles/custom-installers.md).

This Custom Installer for Composer will trigger on the following library types
and provide custom behaviour for those.

* *phpdocumentor-template*, install files into /data/template instead of /vendor

Usage
-----

In order to tell a template to use this installer you need to add the following
*composer.json*

```
{
    "name": "phpdocumentor/template-$NAME$",
    "type": "phpdocumentor-template",
    "license": "MIT"
    "repositories":[
        { "type":"git", "url":"http://github.com/phpDocumentor/UnifiedAssetInstaller" }
    ],
    "require": {
        "phpdocumentor/unified-asset-installer":"*"
    }
}
```

The type element will instruct Composer to use this Custom Installer.

TODO
----

* Add the *phpdocumentor-plugin* library type as well.
* install a custom Packagist or Satis instance instead of using the direct git
  repo

FAQ
---

## What's up with the name?

Due to a [bug in Composer](https://github.com/composer/composer/issues/655) at
time of writing of this document must the name be alphabetically LATER than the
word *template*.