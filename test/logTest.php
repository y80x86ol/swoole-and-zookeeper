<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-23
 * Time: 09:02
 */
require dirname(__DIR__) . '/vendor/autoload.php';

$config = \App\Common\Log::getInstance();

$config::info("hello 1");

\App\Common\Log::info("hello 2", ["name" => "ken"], ["234234" => "k222en"]);