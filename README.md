
云前的一个封装

### 安装

```bash
git clone https://github.com/pihizi/gini-cloudfs.git cloudfs
gini composer init
composer install
gini cache && gini config update
```

### 调用方法

#### 1. 部署依赖关系: 在gini.json中声明依赖
```javascript
{
   "dependencies": {
     ...
     "cloudfs": "*",
     ...
   }
}
```
#### 2. 初始化配置文件

##### 2.1. cloudfs.yml
```bash
---
# client在担任client的服务器需要指定client项
client:
   # 在没有指定选择的cloud时，默认使用的配置，在client角色的站点配置
   default: qiniu_client
   # 各类cloud的配置信息，如qiniu, abc
   qiniu_client:
       driver: Qiniu
       rpc:
           url: "http://cloudfs.gapper.in/api"
           server: qiniu_server
           client_id: CLIENTID
           client_secret: CLIENTSECRET
       callbacks:
           # 允许各个app在上传操作开始前进行逻辑判断并可通过返回false阻断上传
           prepare: "\Name\To\Class::method"
           success: "\Name\To\Class::method"
           fail: "\Name\To\Class::method"
           always: "\Name\To\Class::method"
       options:
           #mode: direct or via-server
           mode: direct
           #以下是七牛支持的自定义变量
           callback_url: http://YOUR-DOMAIN/ajax/cloudfs/qiniu/callback
           callback_body: key=$(key)&client=qiniu_client&hash=$(etag)

# 在担任server的服务器需要指定server项
server:
   default: qiniu_server
   qiniu_server:
       driver: Qiniu
       # 配置可以访问CloudFS Server的客户端
       clients:
           CLIENTID: CLIENTSECRET
       callbacks:
           success: "\Name\To\Class::method"
           fail: "\Name\To\Class::method"
           always: "\Name\To\Class::method"
       options:
           #不同的cloud可能或有不同的配置
           # 比如：qiniu会有bucket，而ftp的方式可能就没有
           bucket: BUCKETNAME
           accessKey: CLOUDACCESSKEY
           secretKey: CLOUDSECRETKEY
...

```
#### 3. 前端调用
```javascript
require(['cloudfs'], function(CloudFS) {
 CloudFS.upload('qiniu_client', file, {
     progress: function(progress) {
        // progress: {
        //      total: NUMBER,
        //      percent: NUMBER
        // }
     },
     success: function(data) {
        // data: response from server
     },
     error: function(evt) {
        // evt: xmlhttprequest error event
     },
     abort: function(evt) {
        // evt: xmlhttprequest abort event
     },
     always: function(evt) {
        // evt: xmlhttprequest load/error/abort event
     }
 });

 // or deferred mode
 CloudFS.upload('qiniu_client', file)
 .progress(function(){})
 .success(function(){})
 .fail(function(){})
 .abort(function(){})
 .always(function(){});

});
```
