Creating an issue
-----------------

For improvement of [issue triage](issue-workflow.md), you can write an issue with next information.

### 1. Versions

Append a software version provide a easy way to test and reproduce the issue by developers.

This is the list of the required software version.

- Centreon Web (In Centreon Web on login page or on Administration > About)
- Centreon Engine (In a terminal on your monitoring server /usr/sbin/centengine -V)
- Centreon Broker version (In a terminal on your monitoring server /usr/sbin/cbd -v)
- PHP version (In a terminal on your monitoring server /usr/bin/php -v)
- MySQL/MariaDB version (In a terminal on your database server /usr/bin/mysql --version)
- Operating system used to run Centreon Web for a WebUI problem (lsb_release -cr)
- Operating system used to run Centreon Engine for a problem to execute a check (lsb_release -cr)

If your issue is about a documentation, you must give the link of the page.

### 2. Log and code

For append logs or code in your issue, this block must be indented with 4 spaces.

You can activate highlighting for language with ```language_name ```

You can found on [Github](https://guides.github.com/features/mastering-markdown/) more information for formatting your comment.


### 3. Example

Title: Problem with edit an user

Comment:

    When I edit an user, the field username is always blank.
    
    - Centreon Web: 2.6.6
    - Centreon Engine: 1.4.1
    - Centreon Broker: 2.10.0
    - PHP: 5.3.3
    - MariaDB: 10.1
    - OS: CentOS 6.7
    
    Log information:
    
        PHP Notice: $username not found
        #1 Line 233...
        
    Fix line 232
    
    ```php
    <?php
    $username = '';
    ```

----

Render:

When I edit an user, the field username is always blank.
    
- Centreon Web: 2.6.6
- Centreon Engine: 1.4.1
- Centreon Broker: 2.10.0
- PHP: 5.3.3
- MariaDB: 10.1
- OS: CentOS 6.7

Log information:

    PHP Notice: $username not found
    #1 Line 233...

Fix line 232

```php
<?php
$username = '';
```
----

**Thank you for contributing to Centreon**