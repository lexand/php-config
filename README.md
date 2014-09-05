php-config
==========

loading/reloading/validating INI files

Support default values.

Example
-------

```
<?php

$defaults = [
   'server' => [
       'dbHost' => '127.0.0.1',
       'idleTimeout' => 10
   ]
];

$rules = [
   'server' => [       // points to section 'server'
       'dbHost' => 'IPv4', // param without section
       'idleTimeout' => 'int' // param in section 'server'
       'logDir' => ['directory', 'baseDir' => __DIR__, 'checkWritable' => true]
   ]
];

$validator = new CompositeConfigValidator($rules)

$config = new ConfigLoader('path_to_ini_file', $defaults, $validator);

// ...

function sighupHandler() use ($config){
   $config->reload()
   // re-init resources with new config
}

```

