## 定制 nginx、php-fpm、redis 的 dockerfile

##### nginx:1.17.8-centos8
```
# 构建镜像
docker build -t nginx:1.17.8-centos8 -f nginx-source-dockerfile

# 简单用法（简单覆盖匿名卷）
docker run -d -p 80:80 -v $PWD/logs:/usr/local/nginx/logs \
    -v $PWD/html:/usr/local/nginx/html \
    --name nginx-prince nginx:1.17.8-centos8

# 进阶用法（覆盖所有匿名卷）。确定本地已经下载好 nginx.conf 和 default.conf(包含 localhost 的 server 层)
docker run -d -p 80:80 --network myNetwork -v $PWD/conf/conf.d:/usr/local/nginx/conf/conf.d \
    -v $PWD/conf/nginx.conf:/usr/local/nginx/conf/nginx.conf \
    -v $PWD/logs:/usr/local/nginx/logs \
    -v $PWD/html:/usr/local/nginx/html \
    --name nginx-prince nginx:1.17.8-centos8

```



##### php:7.4.5-fpm-alpine-redis-swoole
```
# 构建镜像
docker build -t php:7.4.5-fpm-alpine-redis-swoole .

# 简单用法
docker run -d --name php-prince php:7.4.5-fpm-alpine-redis-swoole

# 简单用法
docker run -d -p 9000:9000 -v $PWD/html/:/var/www/html \
    --name php-prince php:7.4.5-fpm-alpine-redis-swoole

# 进阶用法
docker run -d -p 9000:9000 --network myNetwork -v $PWD/conf/php.ini:/usr/local/etc/php/php.ini \
    -v $PWD/conf/www.conf:/usr/local/etc/php-fpm.d/www.conf \
    -v $PWD/html/:/var/www/html \
    --name php-prince php:7.4.5-fpm-alpine-redis-swoole

# 进入已启动的容器中
docker exec -it php-prince sh
    报错：OCI runtime exec failed: exec failed: container_linux.go:346: starting container process caused "exec: \"bash\": executable file not found in $PATH": unknown
    解决：bash 命令换成 sh 命令
```



#### redis:5.0.8-alpine

```
# 构建镜像
docker build -t redis:5.0.8-alpine .

# 简单用法
docker run -d -p 6379:6379 -v $PWD/data:/usr/src/redis/data \
    --name redis-prince redis:5.0.8-alpine
    
# 进阶用法
docker run -d -p 6379:6379 --network myNetwork -v $PWD/data:/usr/src/redis/data \
    --name redis-prince redis:5.0.8-alpine
    
# 进入已启动的容器中
docker run -it redis-prince sh
```

