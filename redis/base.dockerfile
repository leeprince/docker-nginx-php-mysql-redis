# 通过选择更小的镜像，删除不必要文件清理不必要的安装缓存，从而瘦身镜像
# 如果直接 {FROM redis:5.0-alpine} = docker pull reids:5.0-alpine 则无需 dockerfile 可以有完整环境了。除非有特殊定制需求，强烈推荐使用官方现成镜像
FROM alpine

MAINTAINER leeprince@foxmail.com

ENV REDIS_VERSION=redis-5.0.8

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories \
  && apk add gcc g++ autoconf libc-dev wget vim openssl-dev make linux-headers \
  && rm -rf /var/cache/apk/*

RUN mkdir -p /usr/local/etc/redis \
    && mkdir -p /usr/local/etc/redis/data \
    && mkdir -p /usr/local/etc/redis/log

RUN mkdir -p /tmp/redis \
    && wget -O /tmp/redis/$REDIS_VERSION.tar.gz http://download.redis.io/releases/$REDIS_VERSION.tar.gz \
    && tar -xzf /tmp/redis/$REDIS_VERSION.tar.gz -C /usr/local/etc/redis

RUN cd /usr/src/redis/$REDIS_VERSION && make && make PREFIX=/usr/local/etc/redis install \
    && ln -s /usr/local/etc/redis/bin/* /usr/local/bin/ \
    && rm -f /tmp/redis/$REDIS_VERSION.tar.gz

VOLUME ["/usr/local/etc/redis/data"]

EXPOSE 6379

CMD ["/usr/local/bin/redis-server", "/usr/local/etc/redis/conf/redis.conf"]