<?php
/**
 * [Redis sentinel 模式下的客户端连接]
 *
 * @Author  leeprince:2020-05-15 14:37
 */


$maxNum = 3;
Retry:

if ($maxNum > 0) {
    var_dump("我重试了 {$maxNum}");
    $maxNum--;
    goto Retry;
}



