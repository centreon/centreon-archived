# Performance test

Here are my performance tests, you can view the results ordered from faster to slower.

| Adapter         | 10.000 set    | 10.000 has    | 10.000 get |
| :-------------- | -----------:  | -----------:  | ---------: | 
| NoCache         | 0.0637        | 0.0482        | 0.0488     | 
| Apc             | 0.0961        | 0.0556        | 0.0770     | 
| File            | 0.6881        | 0.3426        | 0.3107     | 
| Mongo           | 13.8144       | 30.0203       | 24.4214    | 


## how i run the test? 

The test its the same for all Adapters and look like this.

``` php
    <?php

    $timer = new Timer();
    for ($i = 1; $i <= 10000; $i++) {
        $cache->set(md5($i), md5($i), 3600);
    }
    $timer->mark('10.000 set');
    for ($i = 1; $i <= 10000; $i++) {
        $cache->has(md5($i));
    }
    $timer->mark('10.000 has');
    for ($i = 1; $i <= 10000; $i++) {
        $cache->get(md5($i));
    }
    $timer->mark('10.000 get');

```

 if you want run the tests them execute.

``` sh
    php test/performance/AdapterName.php
```