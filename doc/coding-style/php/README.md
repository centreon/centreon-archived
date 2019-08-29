# Coding Style Guide

## PHP

For these projects, Centreon work on follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style guidelines.

[Changelog from PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide-meta.md#5-changelog-from-psr-2)

### Summary

* Code must use an indent of 4 spaces, and must not use tabs for indenting.
* There must not be trailing whitespace at the end of non-blank lines.
* The PHP constants true, false, and null must be in lower case.
```php
//bad
public function sampleMethod ($a, $b = NULL) {}
//good
public function sampleMethod ($a, $b = null)
{
    // method body
}
```
* For control structures( if/for/while…), the placement of parentheses, spaces, and braces; and that else and elseif are on the same line as the closing brace from the earlier body.-b
```php
//bad
if($a===$b){
    echo 'equal';
}
//good
if ($a === $b) {
    echo 'equal';
}
```
* The keyword elseif should be used instead of else if so that all control keywords look like single words.

```php
namespace Vendor\Package;

use FooInterface;
use BarClass as Bar;
use OtherVendor\OtherPackage\BazClass;

class Foo extends Bar implements FooInterface
{
    public function sampleMethod ($a, $b = null)
    {
        if ($a === $b) {
            bar();
        } elseif ($a > $b) {
            $foo->bar($arg1);
        } else {
            BazClass::bar($arg2, $arg3);
        }

        foreach ($iterable as $key => $value) {
             // foreach body
        }

        echo 'A string with ' . $someVariable . ' and ' . $otherVariable;
    }

    final public static function bar()
    {
        // method body
    }
}

```
* The limit on line length must be 120 characters, 80 is better.
```php
public function longLine (
    $longArgument,
    $longerArgument,
    $muchLongerArgument
) {

    $longArray = array(
        array(
            0,
            1,
            2
        ),
        3,
        4
    );

    $longString = 'Some String with ' . (string)$someVariable . ' and ' .
        'Concatinated';

    if (
        ($a == $b)
        && ($b == $c)
        || ($c == $d)
    ) {
        $a = $d;
    }
}
```
* Method and variable names must be in lowerCamelCase.

```php
//bad
$compound_name = 'compound';
$compound-name = 'compound';

//good
$compoundName = 'compound';
```
* For the casting, please use (int)$var instead of intval($var) method.

```php
$b = true;
$i = "1";
$f = "1.5";

//bad
boolval($b);
intval($i);
floatval($f);

//good
(bool)$b;
(int)$i;
(float)$f;
```
### Check your code

To check your code, you can use [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer), it is available with composer:
```bash
$ php composer.phar require --dev \ squizlabs/php_codesniffer:"*@stable"
```
To validate the code with the [PSR-12](http://www.php-fig.org/psr/psr-12/) standard:
```bash
$ ./bin/phpcs -p --standard=PSR12 src/centreon/myFile
```

**[⬆ back to top](#coding-style-guide)**

**[← back to summary](https://github.com/centreon/centreon)**
