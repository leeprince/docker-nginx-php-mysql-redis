<?php
/**
 * [监控 redis 主从复制偏移量：当监控到延迟高的节点时，移除延迟高的从节点， 即动态修改读写分离配置信息。(可以添加触发报警机制)]
 *      [在做主从复制时推荐使用 redis 的哨兵模式，则无需自己去监控，哨兵模式能自己监控并切换主从]
 *
 * @Author  leeprince:2020-05-05 10:29
 */

define('IS_DEBUG', false);
define('MAX_OFFSET', 10);
$redis = new Redis();
$redis->connect("127.0.0.1", 63790);
$redis->auth("leeprince");

$info         = $redis->info('replication');
$slaveNum     = $info['connected_slaves'];
$masterOffest = $info['master_repl_offset'];

echo "+++++++++++++++++++++++定时开始+++++++++++++++++++++++\r\n";

$invalidSlave = []; // 要移除的从节点
for ($i = 0; $i < $slaveNum; $i++) {
    $slaveKey    = "slave{$i}";
    $slaveInfo   = $info[$slaveKey];
    $slaveInfo   = explode(',', $slaveInfo);

    $slaveHost   = explode('=', $slaveInfo[0])[1];
    $slavePort   = explode('=', $slaveInfo[1])[1];
    $slaveState  = explode('=', $slaveInfo[2])[1];
    $slaveOffset = explode('=', $slaveInfo[3])[1];

    $offset = $masterOffest - $slaveOffset;
    if ($offset > MAX_OFFSET || $slaveState != 'online') {
        $readHostMap = include_once "./hostMap.php";
        $slaveString = "tcp://{$slaveHost}:{$slavePort}";
        $invalidSlave[] = $readHostMap[$slaveString];
        var_dump("当前从节点为高延迟--{$slaveString}");
    }
}

if (empty($invalidSlave)) {
    if (IS_DEBUG) {
        echo "========================================\r\n";
        var_dump($info);
        echo "========================================\r\n";
    }
    var_dump('不存在需要移除的从节点');
    exit(0);
}

// redis 动态配置文件
$confPath = './config.php';
include $confPath;


if (IS_DEBUG) {
    echo "========================================\r\n";
    var_dump($info);
    var_dump($replication);
    echo "========================================\r\n";
}

$isHaveChange = false; // 是否需要修改配置。考虑上一次可能已经移除了该从节点
foreach ($replication as $key => &$value) {
    if (in_array($value, $invalidSlave)) {
        $isHaveChange = true;
        var_dump("需要移除的从节点存在当前配置中--{$value}");
        unset($replication[$key]);
    }
}

if ($isHaveChange) {
    $content = "<?php \n\$replication = ".var_export($replication, true).";";
    file_put_contents($confPath, $content);
    var_dump('已修改配置文件--', $content);
} else {
    var_dump('无需修改配置文件');
}
exit(0);









