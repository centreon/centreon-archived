# FinderFacade

**FinderFacade** is a convenience wrapper for Symfony's [Finder](http://symfony.com/doc/2.2/components/finder.html) component.

## Installation

You can use the [PEAR Installer](http://pear.php.net/manual/en/guide.users.commandline.cli.php) or [Composer](http://getcomposer.org/) to download and install this package as well as its dependencies.

### PEAR Installer

The following two commands (which you may have to run as `root`) are all that is required to install this package using the PEAR Installer:

    pear config-set auto_discover 1
    pear install pear.phpunit.de/FinderFacade

### Composer

To add this package as a local, per-project dependency to your project, simply add a dependency on `sebastian/finder-facade` to your project's `composer.json` file. Here is a minimal example of a `composer.json` file that just defines a dependency on FinderFacade 1.1:

    {
        "require": {
            "sebastian/finder-facade": "1.1.*"
        }
    }
