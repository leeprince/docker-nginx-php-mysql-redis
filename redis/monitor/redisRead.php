<?php
/**
 * [redis 读写分离]
 *
 * @Author  leeprince:2020-05-05 10:29
 */
require_once "vendor/autoload.php";

// redis 动态配置文件
include "config.php";

/** @var 主从复制模式：Replication $parameters */
$parameters =  $replication;
// $parameters =  ['tcp://127.0.0.1:63790?alias=master', 'tcp://127.0.0.1:63791'];
$options = ['replication' => true, 'parameters' => ['password' => 'leeprince']];
$client = new Predis\client($parameters, $options);


var_dump($client->get('prince'));

var_dump($client->set('prince', 'pp'));
var_dump($client->get('prince'));






