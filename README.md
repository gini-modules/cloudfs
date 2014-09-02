
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
   default: qiniu
   # 各类cloud的配置信息，如qiniu, abc
   qiniu:
       driver: Qiniu
       rpc:
           url: "http://cloudfs.gapper.in/api"
           server: qiniu
           client_id: CLIENTID
           client_secret: CLIENTSECRET
       callbacks:
           # 允许各个app在上传操作开始前进行逻辑判断并可通过返回false阻断上传
           prepare: "\Name\To\Class::method"
           success: "\Name\To\Class::method"
           fail: "\Name\To\Class::method"
           always: "\Name\To\Class::method"
       options:
           # 自定义数据
           # 数据传送给browser，browser在向云端上传文件时，将附带这些数据
           # 系统占用了'cfs:'开头的自定义变量，请使用时注意
           #以下是七牛支持的自定义变量
           #x:callbackUrl: http://YOUR-DOMAIN/ajax/cloudfs/qiniu/callback
           # CloudFS定义的，用无状态的保持client name
           #x:client: qiniu
           #x:callbackBody: key=$(key)&client=$(x:client)&hash=$(etag)

# 在担任server的服务器需要指定server项
server:
   default: qiniu
   qiniu:
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
       client_options:
           mode: "direct"  # or "via-server"
           # params
           # 上传文件的地址
           url: http://up.qiniu.com/
           #也可以上传到本地
           #url: /ajax/cloudfs/qiniu/upload
           #browser/js上传成功后的客户端回调函数
           callback: /ajax/cloudfs/qiniu/parseData
           # params被预留了，请不要使用
...

```
#### 3. 前端调用
```javascript
require(['cloudfs'], function(CloudFS) {
 var fs = new CloudFS('qiniu');

 fs.upload(file, {
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
 fs.upload(file)
 .progress(function(){})
 .success(function(){})
 .fail(function(){})
 .abort(function(){})
 .always(function(){});

});
```
