php-config
==========

loading/reloading/validating INI files

Support default values.

Example
-------

```
<?php

$defaults = [
   'dbHost' => '127.0.0.1',
   'server' => [
       'idleTimeout' => 10
   ]
];

$rules = [
   'dbHost' => 'IPv4', // param without section
   'server' => [       // points to section 'server'
       'idleTimeout' => 'int' // param in section 'server'
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

