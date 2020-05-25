<?php
/**
 * [Description]
 *
 * @Author  leeprince:2020-05-24 20:19
 */

class WorkerEpoll
{
    public  $onMessage; // 绑定一个消息触发的事件回调
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
        // 添加一个 socket 到 epoll 监听列表当中
        swoole_event_add($this->_mainSocket, function ($fd) {
            // var_dump($fd);
            $clientSocket = stream_socket_accept($this->_mainSocket);
    
            // 添加一个 socket 到 epoll 监听列表当中, 当 socket 状态发生改变的时候执行事件回调
            swoole_event_add($clientSocket, function ($fd) {
                // 检查连接是否关闭
                if (feof($fd) || !is_resource($fd)) {
                    swoole_event_del($fd);
                    // 尝试触发 onClose 回调
                    fclose($fd);
                    return null;
                } else {
                    $request = fread($fd, 65535);
                    if (is_callable($this->onMessage)) {
                        call_user_func($this->onMessage, $fd, $this, $request);
                    }
                }
            });
        });
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
        $http_resonse .= "Content-length: " . strlen($content) . "\r\n\r\n";
        $http_resonse .= $content;
        fwrite($fd, $http_resonse);
    
        // fclose($fd); // 返回响应后直接关闭文件描述符
    }
}