<?php
/**
 * [Description]
 *
 * @Author  leeprince:2020-05-25 22:34
 */

/** 基础测试 */
/*$pid = pcntl_fork();
if ($pid < 0) {
    exit('子进程创建失败');
} else if ($pid == 0) {
    // 子进程空间; 虽然在同一个文件但是不同的子进程或者父进程拥有不同的内存
    sleep(1);
    var_dump('子进程打印：', $pid);
} else {
    // 父进程空间; 父进程空间返回子进程ID
    var_dump('父进程得到子进程ID：', $pid);
}*/

/** @var 应用：数据整理，多邮件发送 $pid */
$data = [1, 2, 3];
foreach ($data as $datum) {
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('子进程创建失败');
    } else if ($pid == 0) {
        // 子进程空间; 虽然在同一个文件但是不同的子进程或者父进程拥有不同的内存
        /** 注意：
         * 回收子进程，防止出现僵尸进程
         * 父进程结束了， 但是子进程还在运行，则子进程成为孤儿进程
         */
        sleep(1);
        var_dump('子进程打印：', $pid);
        exit; // 结束子进程，继续执行，防止循环嵌套创建子进程
    } else {
        // 父进程空间; 父进程空间返回子进程ID
        var_dump('父进程得到子进程ID：', $pid);
    }
}
// // 回收子进程
$pid = pcntl_wait($status, WUNTRACED);
var_dump('回收子进程ID:', $pid);


