## 定制 nginx、php-fpm、redis 的 dockerfile

##### nginx:1.17.8-centos8
```
# 构建镜像
cd nginx
docker build -t leeprince/nginx .

# 简单用法（简单覆盖匿名卷）
docker run -d -p 80:80 -v $PWD/logs:/usr/local/nginx/logs \
    -v $PWD/html:/usr/local/nginx/html \
    --name nginx-prince leeprince/nginx

# 进阶用法（覆盖所有匿名卷）。确定本地已经下载好 nginx.conf 和 default.conf(包含 localhost 的 server 层)
docker run -d -p 80:80 --network myNetwork -v $PWD/conf/conf.d:/usr/local/nginx/conf/conf.d \
    -v $PWD/conf/nginx.conf:/usr/local/nginx/conf/nginx.conf \
    -v $PWD/logs:/usr/local/nginx/logs \
    -v $PWD/html:/usr/local/nginx/html \
    --name nginx-prince leeprince/nginx

```



##### php:7.4.5-fpm-alpine-redis-swoole
```
# 构建镜像
cd php
docker build -t leeprince/php:7.4.5-fpm-alpine-redis-swoole .

# 简单用法
docker run -d --name php-prince leeprince/php:7.4.5-fpm-alpine-redis-swoole

# 简单用法
docker run -d -p 9000:9000 -v $PWD/html/:/var/www/html \
    --name php-prince leeprince/php:7.4.5-fpm-alpine-redis-swoole

# 进阶用法
docker run -d -p 9000:9000 --network myNetwork -v $PWD/conf/php.ini:/usr/local/etc/php/php.ini \
    -v $PWD/conf/www.conf:/usr/local/etc/php-fpm.d/www.conf \
    -v $PWD/html/:/var/www/html \
    --name php-prince leeprince/php:7.4.5-fpm-alpine-redis-swoole

# 进入已启动的容器中
docker exec -it php-prince sh
    报错：OCI runtime exec failed: exec failed: container_linux.go:346: starting container process caused "exec: \"bash\": executable file not found in $PATH": unknown
    解决：bash 命令换成 sh 命令
```



#### redis

```
## 构建镜像
cd redis
docker build -t leeprince/redis .

## 简单用法
docker run -d -p 6379:6379 -v $PWD/data:/usr/src/redis/data \
    --name redis-prince leeprince/redis
    
## 进阶用法
docker run -d -p 6379:6379 --network myNetwork -v $PWD/data:/usr/src/redis/data \
    --name redis-prince leeprince/redis
    
## 进入已启动的容器中
docker run -it redis-prince sh


# 官方镜像：FROM redis
## 主从配置; 当前环境redis:v1 = leeprince/redis:latest。注意检查镜像和文件路径
docker run -d --network myNet -p 63790:6379 -v $PWD/master:/usr/local/etc/redis --name redis-m redis:v1 
docker run -d --network myNet -p 63791:6379 -v $PWD/slave01:/usr/local/etc/redis --name redis-s01 leeprince/redis
## 测试主从复制延迟；--privileged(容器将拥有访问主机所有设备的权限)
docker run -d --privileged --network myNet -p 63792:6379 -v $PWD/slave02:/usr/local/etc/redis --name redis-s02 leeprince/redis
    
    tc qdisc add dev eth0 root netem delay 5000ms
    tc qdisc del dev eth0 root netem delay 5000ms
```


#### mysql 

```
构建镜像
cd mysql
docker build -t leeprince/mysql .

# 主
docker run -d --network myNet -p 33070:3306 -v $PWD/master/conf/mysql.cnf:/etc/mysql/conf.d/mysql.cnf -v $PWD/master/data:/var/lib/mysql/ -e MYSQL_ROOT_PASSWORD=leeprince --name mysql-m leeprince/mysql
# 从
docker run -d --network myNet -p 33071:3306 -v $PWD/slave01/conf/mysql.cnf:/etc/mysql/conf.d/mysql.cnf -v $PWD/slave01/data:/var/lib/mysql/ -e MYSQL_ROOT_PASSWORD=leeprince --name mysql-s01 leeprince/mysql

# 用于主从复制的用户
create user "slave"@"%" identified by "slave";

# 授权
grant all privileges on *.* to "slave"@"%" with grant option;

# 刷新权限
flush privileges;

```

#### docker-composer
```
docker-compose up

docker-compose stop

docker-compose start
docker-compose restart



```

