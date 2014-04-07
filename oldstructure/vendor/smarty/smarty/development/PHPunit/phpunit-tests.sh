#!/bin/sh
php -d asp_tags=On /usr/local/bin/phpunit --verbose SmartyTests.php > test_results.txt
