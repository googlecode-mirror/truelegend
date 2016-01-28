[English](http://translate.google.com.hk/translate?u=http%3A%2F%2Fcode.google.com%2Fp%2Ftruelegend%2F&sl=zh-CN&tl=en&hl=&ie=UTF-8)

# Introduction #
**TrueLegend**
是一个基于HTTP协议的简单消息队列系统，使用PHP5语言实现。

[下载TrueLegend 1.0GA](http://truelegend.googlecode.com/files/truelegend-1.0.tar.gz)

# Features #
  * 基于HTTP协议，支持多客户端编程语言调用
  * 准实时处理，通过Daemon进程常驻内存
  * 支持使用File、Database做数据的持久化存储
  * 支持FIFO、LIFO、Priority的队列存储结构
  * 安装部署简单，通过Installer实现0配置
  * 支持多队列，每个队列实现各自的业务逻辑

# Install #
**Show help:**
```
[root@ truelegend]# php install.php help
Usage:
    php install.php <option>

Options:
    install             - Create the queue related directory, the first time in the new server installation to use
    uninstall           - Delete the queue directory
    newapp appname      - Create a new application related to the directory
    delapp appname      - Delete a application related directory
    help                - Show help
```
**Install:**
```
[root@ truelegend]# php install.php install

Begin install.....

[SUCC] Create app dir /data/home/zendzhang/truelegend/source/App ok!
[SUCC] Create logs dir /data/home/zendzhang/truelegend/logs  ok!
[SUCC] Create data dir  /data/home/zendzhang/truelegend/data  ok!
[SUCC] Create monitor dir /dev/shm/truelegend ok!
[SUCC] Create dir /elink ok!
[SUCC] Create symbolic link /elink/truelegend ok!

Done!
```
**Create a new app queue "demo":**
```
[root@ truelegend]# php install.php newapp demo

Begin new app Demo .....

[SUCC] Create app logs dir. /elink/truelegend/logs/Demo ok!
[SUCC] Create app data dir /elink/truelegend/data/Demo ok!
[SUCC] Created app source dir /elink/truelegend/source/App/Demo  ok!

Done!
```
# Usage #
**Start "demo" queue:**
```
[root@ truelegend]# php /elink/truelegend/source/App/Demo/Daemond.php start
Daemon starting...
Running...
```
**Sent message into "demo" queue:**
```
[root@ truelegend]# curl -d "queuename=Demo&queuedata=HelloWorld" "http://localhost/SendMessage.php"
```
or
```
<?php

$data = array
(
    'queuename' => 'Demo',
    'queuedata' => 'HelloWorld'
);

$result = json_decode(httpPost('localhost', '/SendMessage.php', $data), true);
if (false === $result)
{
    echo "Request failed!\n";
}
else
{    
    if (isset($result['status']) && $result['status'] == 1)
    {
        echo "Send message successful!\n";
    }
    else
    {
        echo "Send message failed,error:{$result['data']}\n";
    }
}
?>
```
**Stop "demo" queue:**
```
[root@ truelegend]# php /elink/truelegend/source/App/Demo/Daemond.php stop
Daemon stopping...
Daemon stoped
```