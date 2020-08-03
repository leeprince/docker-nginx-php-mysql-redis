<?php
/**
 * [Description]
 *
 * @Author  leeprince:2020-05-25 22:34
 */

/** 基础测试：
 * 虽然在同一个文件但是不同的子进程直接或者子进程与父进程之间都是独立的，都拥有不同的内存。
 */
/*$pid = pcntl_fork();
if ($pid < 0) {
    exit('子进程创建失败');
} else if ($pid == 0) {
    // 子进程空间
    sleep(1);
    var_dump('子进程打印：'.$pid);
} else {
    // 父进程空间; 父进程空间返回子进程ID
    var_dump('父进程得到子进程ID：'.$pid);
}*/

// declare 结构用来设定一段代码的执行指令。declare 的语法和其它流程控制结构相似
// 要能执行信号的处理，需要使用 declare 声明一个 ticks: Tick（时钟周期）是一个在 declare 代码段中解释器每执行 N 条可计时的低级语句就会发生的事件。
declare(ticks = 1);

/** @var 应用：数据整理，多邮件发送 $pid */
$data = [1, 2, 3];
foreach ($data as $item) {
    /**
     * 在当前进程当前位置产生分支（子进程）。
     * fork 是创建了一个子进程，父进程和子进程 都从 fork 的位置开始向下继续执行，不同的是父进程执行过程中，得到的 fork 返回值为子进程号，而子进程得到的是0。
     */
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('子进程创建失败');
    } else if ($pid == 0) {
        // 子进程空间;
        /** 注意：
         * 回收子进程，防止出现僵尸进程
         * 父进程结束了， 但是子进程还在运行，则子进程成为孤儿进程
         */
        sleep(20);
        var_dump('子进程打印：'.$pid);
        exit; // 结束子进程，继续执行，防止循环嵌套创建子进程
    } else {
        // 父进程空间; 父进程空间返回子进程ID
        var_dump('父进程得到子进程ID：'.$pid);
    }
}

// 回收子进程: 创建多少进程即回收多少次
foreach ($data as $v) {
    // nginx -s reload 重启 worker 进程
    // 安装一个信号处理器：pcntl_signal()。信号编号查看：kill -l
    pcntl_signal(2, function ($signo) {
        var_dump('信号编号：'.$signo);
        // php: 杀死进程 posix_kill();
        // posix_kill(工作进程)
        // 重新拉起工作进程
    });
    
    $pid = pcntl_wait($status, WUNTRACED);
    var_dump('回收子进程ID:'.$pid);
}


