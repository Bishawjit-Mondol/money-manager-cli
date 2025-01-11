#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\MoneyManagerCommand;

$application = new Application();
$application->add(new MoneyManagerCommand());
$application->run();
