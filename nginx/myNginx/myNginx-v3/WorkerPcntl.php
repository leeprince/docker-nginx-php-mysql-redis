<?php
/**
 * [Description]
 *
 * @Author  leeprince:2020-05-24 20:19
 */

class WorkerPcntl
{
    public  $addr; // socket 地址：协议+ip+端口
    public  $onMessage; // 绑定一个消息触发的事件回调
    private $_mainSocket; // 保存 socket 服务端资源
    public $workNum; // worker 进程数量
    
    /**
     * Worker constructor. 初始化：创建 socket  -> 绑定服务器的协议 + IP + 端口 -> 监听端口
     *
     * @param $addr
     */
    public function __construct($addr)
    {
        $this->addr = $addr;
    }
    
    /**
     * [启动服务]
     *
     * @Author  leeprince:2020-05-24 22:46
     */
    public function runAll()
    {
        for ($i = 0; $i < $this->workNum; $i++) {
            $pid = pcntl_fork();
            if ($pid < 0) {
                exit('子进程创建失败');
            } else if ($pid == 0) {
                // 子进程空间; 虽然在同一个文件但是不同的子进程或者父进程拥有不同的内存
                /** 注意：
                 * 回收子进程，防止出现僵尸进程
                 * 父进程结束了， 但是子进程还在运行，则子进程成为孤儿进程
                 */
                // sleep(1);
                // var_dump('子进程打印：'.$pid);
                $this->listen();
                exit; // 停止继续执行，防止循环嵌套创建子进程
            } else {
                // 父进程空间; 父进程空间返回子进程ID
                var_dump('父进程得到子进程ID：'.$pid);
            }
        }
        for ($i = 0; $i < $this->workNum; $i++) {
            $pid = pcntl_wait($status, WUNTRACED);
            // var_dump('回收子进程ID:'.$pid);
        }
    }
    
    /**
     * [监听服务端发送的请求]
     *
     * @Author  leeprince:2020-05-24 20:25
     */
    public function listen()
    {
        $content = stream_context_create([
          'socket'  => [
              'backlog' => 10000,
              'so_reuseport' => true, // 允许多进程监听同一个端口
          ]
        ]);
        // 代替了原生的三步骤： socket_create();->socker_bind();->socker_listen();
        $this->_mainSocket = stream_socket_server($this->addr,$errno,$errstr,STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $content);
        
        // 添加一个 socket 到 epoll 监听列表当中; swoole_event_add 是 swoole 面向过程函数；Swoole\Event::add 是面向对象方法
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