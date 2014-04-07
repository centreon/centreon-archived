quickform
=========

PHP 5.4 compatible fork of HTML_QuickForm

This package is intended mainly as a drop-in replacement for existing installations of ``HTML_Quickform``. See http://pear.php.net/package/HTML_QuickForm/docs for documentation.

The main differences to the original package are:

 - Compatible with PHP 5.4: It will run without producing warnings or deprecated notices
 - No PEAR dependencies: ``HTML_Common`` is replaced by a bundled version, and ``PEAR_Error``s are replaced by exceptions
 - Support for Composer autoloading: All ``include`` statements have been removed in favor of classmap autoloading
