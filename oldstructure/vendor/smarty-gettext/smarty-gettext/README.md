**smarty-gettext - Gettext support for Smarty2/Smarty3**
===============================================

[smarty-gettext][1] provides gettext support for [Smarty][2],
the popular PHP templating engine.

This README assumes that you already know what is [gettext][3] and how to
use it with PHP.

If you don't, please visit the following websites before trying to
use this package:

 2. http://www.php.net/gettext
 3. http://www.onlamp.com/pub/a/php/2002/06/13/php.html

If you encounter problems when using the native [gettext][4] extension,
you may want to try the [php-gettext][5] module, which emulates the behavior
of the C extension, but is written in pure PHP.

This package has two parts:

  1. `block.t.php`   - The Smarty plugin.
  2. `tsmarty2c.php` - A command line utility that rips gettext strings
                     from smarty source files and converts them to C format.


**Installation**
----------------

With Composer:

- Add the `"smarty-gettext/smarty-gettext": "@stable"` into the `require` section of your `composer.json`.
- Run `composer install`.

Manually:

- Simply copy `block.t.php` to your Smarty plugins directory.


**block.t.php**
---------------

The Smarty plugin


**Usage**

The content of the block function is the string that you want to translate.
For example, for translating `Hello World`, use: `{t}Hello World{/t}`.

If you have dynamic parameters that should be set inside the string,
pass them to the block function, and they will be replaced with `%n`,
where `n` is `1` for the 1st parameter and so on.

For example, `{t name="sagi"}my name is %1{/t}` will replace `%1` with sagi.

The parameter name is ignored, unless it is one of the reserved
names (see below). Only the parameters order matters.

Example for using multiple parameters:

    {t 1='one' 2='two' 3='three'}The 1st parameter is %1, the 2nd is %2 and the 3nd %3.{/t}

**NOTE:** I decided to use numeric arguments instead of [sprintf()][6],
mainly because its syntax is simpler for the translators
(especially when wanting to change the parameter order).

You can also use this method in your PHP code, by using the
`smarty_gettext_strarg()` function. It is only loaded after `block.t.php` is
included, so you probably want to copy it elsewhere.

I usually name the global version of this `function strarg()`, and use it like this:

    echo strarg(_('hi %1'), $name [,..]);

By default, all the translated strings will be automatically HTML escaped.
You may control this by setting the `escape` parameter. Possible values:

  - `html` for HTML escaping, this is the default.
  - `js` for javascript escaping.
  - `url` for url escaping.
  - `no`/`off`/`0` - disables escaping.

Example:

    {t escape=no url="http://www.php.net/" name="PHP website"}
    <a href="%1">%2</a>
    {/t}

Using variables

Sometimes you need translated block passed as variable. This can be achieved with `capture` block:

    {capture assign="extra_title"}{t}Weekly report{/t}{/capture}
    {include file="header.tpl.html" extra_title=$extra_title}


Plural support
--------------

The package also provides support for plural forms (see ngettext).

To provide a plural form:

  1. Set a parameter named `plural` with the plural version of the string.
  2. Set a parameter named `count` with the variable count.

Plural and count are special parameters, and therefore, are not available
as numeric arguments. If you wish to use the count value inside the string,
you will have to set it again, as a numeric argument.

Example:

    {t count=$files|@count 1=$files|@count plural="%1 files"}One file{/t}

Modifier support
----------------

A Smarty modifier support is not provided by this package.

I believe variables should be translated in the application level
and provided after translation to the template.

If you need it anyway, it is easy to create such modifier, by simply
registering the PHP gettext command as one.

**tsmarty2c.php - the command line utility**
--------------------------------------------

This utility will scan templates for `{t}...{/t}` placeholders for translation strings
and output a `.pot` file (`.po` template).

Usage:

    ./tsmarty2c.php -o template.pot <filename or directory> <file2> <...>

If a parameter is a directory, the template files within will
be parsed, recursively.

In output special PO tags are added that inform about location of extracted translation. Most of the PO edit tools can respect that information.

If you wish to scan also `.php` or `.phtml` files for native gettext calls, you may wish to combine result of `tsmarty2c` and `xgettext` calls:

```
tsmarty2c -o smarty.pot ...
xgettext --add-comments=TRANSLATORS: --keyword=gettext --keyword=_  --output=code.pot ...
msgcat -o template.pot code.pot smarty.pot
rm -f code.pot smarty.pot
```

By default `tsmarty2c` scans for `.tpl` files, if you wish to use other files, you can use `xargs` in unix:

```
find templates -name '*.tpl.html' -o -name '*.tpl.text' -o -name '*.tpl.js' -o -name '*.tpl.xml' | xargs tsmarty2c.php -o smarty.pot
```

See how it's done in [Eventum](https://github.com/eventum/eventum/blob/master/localization/Makefile) project.

**Authors**
-----------

 - Original Author: Sagi Bashari <sagi@boom.org.il>, http://smarty-gettext.sourceforge.net/
 - Current maintainer: Elan Ruusamäe <glen@delfi.ee>

**Copyright**
-------------

Copyright (c) 2004-2005 Sagi Bashari <br>
Copyright (c) 2010-2013 Elan Ruusamäe

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

  [1]: https://github.com/smarty-gettext/smarty-gettext
  [2]: http://www.smarty.net/
  [3]: https://www.gnu.org/software/gettext/
  [4]: http://php.net/manual/en/book.gettext.php
  [5]: https://launchpad.net/php-gettext/
  [6]: http://php.net/manual/en/function.sprintf.php
