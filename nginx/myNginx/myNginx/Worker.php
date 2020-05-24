<?php
/**
 * [Description]
 *
 * @Author  leeprince:2020-05-24 20:19
 */

class Worker
{
    public $onMessage; // 绑定一个消息触发的事件回调
    private $_mainSocket; // 保存 socket 服务端资源
    
    /**
     * Worker constructor. 初始化：创建 socket  -> 绑定服务器的协议 + IP + 端口 -> 监听端口
     *
     * @param $addr
     */
    public function __construct($addr)
    {
        $this->_mainSocket = stream_socket_server($addr); // 代替了原生的三步骤： socket_create();->socker_bind();->socker_listen();
    }
    
    /**
     * [启动服务]
     *
     * @Author  leeprince:2020-05-24 22:46
     */
    public function runAll()
    {
        $this->listen();
    }
    
    /**
     * [监听服务端发送的请求]
     *
     * @Author  leeprince:2020-05-24 20:25
     */
    public function listen()
    {
        while (true) {
            // 阻塞获取客户端的请求
            $clientSocket = stream_socket_accept($this->_mainSocket);
            // var_dump((int)$clientSocket);
            $request = fread($clientSocket, 65535);
            
            if (is_callable($this->onMessage)) {
                call_user_func($this->onMessage, $clientSocket, $this, $request);
            }
        }
    }
    
    /**
     * [响应客户端]
     *
     * @Author  leeprince:2020-05-25 00:12
     * @param $fd
     * @param $content
     */
    public function response($fd, $content)
    {
        $http_resonse = "HTTP/1.1 200 OK\r\n";
        $http_resonse .= "Content-Type: text/html;charset=UTF-8\r\n";
        $http_resonse .= "Connection: keep-alive\r\n";
        $http_resonse .= "Server: php socket server\r\n";
        $http_resonse .= "Content-length: ".strlen($content)."\r\n\r\n";
        $http_resonse .= $content;
        fwrite($fd, $http_resonse);
    }
}