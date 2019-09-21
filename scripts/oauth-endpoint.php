<?php

include __DIR__ . '/../vendor/autoload.php';

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

file_put_contents(__DIR__ . '/../.oauth.code',$request->query->get('code'));

echo 'You may now close this window and return to the command line';