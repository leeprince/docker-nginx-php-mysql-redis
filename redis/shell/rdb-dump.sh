#!/bin/sh

:<<princeComment
todo:
    1. 创建备份的路径
    2. 重新生成备份
    3. 检查备份是否完成
    4. 备份文件到其它路径或者其他地方
    5. 删除过去不需要的备份文件
princeComment

# 1. 创建备份的路径
dirName=$(date +%Y%m%d)
backPath=/Users/leeprince/tmp/${dirName}
backFileName=$(date +%H%M%S)
mkdir -p ${backPath}

# 2. 重新生成备份
# $(redis-cli bgsave) == `redis-cli bgsave`
# 如果 redis 需要密码登录则 `redis-cli -a 密码 bgsave`
cmd=$(redis-cli bgsave)

# 3. 检查备份是否完成
rdb_bgsave_in_progress=$(redis-cli info persistence | grep rdb_bgsave_in_progress | awk -F: '{print $2}')
echo "rdb_bgsave_in_progress：$rdb_bgsave_in_progress"
while [ $rdb_bgsave_in_progress == 1 ] 
do
    sleep 1
    rdb_bgsave_in_progress=$(redis-cli info persistence | grep rdb_bgsave_in_progress | awk -F: '{print $2}')
    echo "while--rdb_bgsave_in_progress：$rdb_bgsave_in_progress"
done

# 4. 备份文件到其它路径或者其他地方
redisRdbPath='/Users/leeprince/redis/dump.rdb'
backFilePath="${backPath}/${backFileName}.rdb"
cp ${redisRdbPath} ${backFilePath}

# 5. 删除过去不需要的备份文件
# 1分之前修改过的
setMmin='+1'
find ${backPath} -mmin ${setMmin} -name *.rdb -exec rm -f {} \;




