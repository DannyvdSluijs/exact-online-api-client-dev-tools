#!/usr/bin/env php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use DevTools\Command\EntityBuilderCommand;
use Symfony\Component\Console\Application;

chdir(__DIR__);

$application = new Application();
$application->add(new EntityBuilderCommand());

$application->run();
