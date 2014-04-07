#!/bin/sh
php -d asp_tags=On /usr/local/bin/phpunit --coverage-html coverage SmartyTests.php > test_results.txt
