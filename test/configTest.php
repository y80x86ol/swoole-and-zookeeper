<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-23
 * Time: 09:02
 */
require dirname(__DIR__) . '/vendor/autoload.php';

$config = \App\Common\Config::getInstance();

print_r($config::getAll());

print_r($config::get("app"));

print_r($config::get("zookeeper.cluster"));