<?php
/**
 * [Description]
 *
 * @Author  leeprince:2020-05-24 20:19
 */
require "WorkerEpoll.php";

// 启动监听当前服务器的 9100 端口，提供服务的是 9100
$worker = new WorkerEpoll('tcp://0.0.0.0:9002');

// 接收到了客户端发送的消息，回调执行（网络事件：消息可读）
$worker->onMessage = function ($fd, $connection, $request) {
    // var_dump($request);
    
    /** 开始写业务逻辑 */
    $connection->response($fd, "利用 swoole 的 event 实现一个单进程 IO 复用网络模型"); // 响应客户端
};

// 启动服务
$worker->runAll();


/**
 * 压力测试：ab -n10000 -c1000 -k http://127.0.0.1:9002/index.php
 */




