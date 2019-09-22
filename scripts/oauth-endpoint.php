<?php

$autoLoaderFiles = ['../vendor/autoload.php', '../../autoload.php', '../../../autoload.php'];
foreach ($autoLoaderFiles as $autoLoaderFile) {
    if (is_readable($autoLoaderFile)) {
        $loader = include $autoLoaderFile;
        break;
    }
}

if (! isset($loader)) {
    die('You must set up the project dependencies.');
}

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

file_put_contents(__DIR__ . '/../.oauth.code',$request->query->get('code'));

echo 'You may now close this window and return to the command line';