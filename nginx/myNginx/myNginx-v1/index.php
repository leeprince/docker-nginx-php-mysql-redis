<?php
/**
 * [Description]
 *
 * @Author  leeprince:2020-05-24 20:19
 */
require "Worker.php";

// 启动监听当前服务器的 9100 端口，提供服务的是 9100
$worker = new Worker('tcp://0.0.0.0:9001');

// 接收到了客户端发送的消息，回调执行（网络事件：消息可读）
$worker->onMessage = function ($fd, $connection, $request) {
    var_dump($request);
    
    /** 开始写业务逻辑 */
    $connection->response($fd, "利用 socket 实现一个单进程堵塞网络模型"); // 响应客户端
};

// 启动服务
$worker->runAll();



/**
 *
 *
 * 单个长连接测试：ab -n1 -c1 -k http://127.0.0.1:9001/index.php
 *
 * 在响应的时候取消注释： fclose($fd); // 返回响应后直接关闭文件描述符。直接关闭后可以支持多个非长连接。否则只能支持单个长连接
 *      多个非长连接测试：ab -n100 -c10 http://127.0.0.1:9001/index.php
 *
 */
