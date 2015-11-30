
云前的一个封装

### 安装

```bash
git clone https://github.com/gini-modules/cloudfs.git cloudfs
gini composer init
composer install
gini cache
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

##### 2.1. `raw/config/cloudfs.yml`
```bash
server:
   default: server1
   # 各类cloud的配置信息，如qiniu, abc
   server1:
       driver: Qiniu
       # 配置可以访问CloudFS Server的客户端
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
   server2:
       driver: LocalFS
       callbacks:
       options:
           # 上传文件请求提交到的地址，默认为ajax/cloudfs/localfs/upload/[LOCALFS]
           url: ...
           # 文件上传的路径，默认为data/cloudfs/localfs
           root: ...
           # 支持上传的文件类型，默认为所有类型. 后缀名
           types:
              - xlsx
              - txt
```
#### 3. 前端调用
```javascript
require(['cloudfs', 'jquery'], function(CloudFS, $) {
 CloudFS.upload('server1', file, {
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
 CloudFS.upload('server1', file)
 .progress(function(){})
 .success(function(){})
 .fail(function(){})
 .abort(function(){})
 .always(function(){});
 
 // or dropbox mode
 var $dropbox = $('#dropbox');
 CloudFS.dropbox({
    server: 'server1',
    container: $dropbox[0],
    dragenter: function() {
        $dropbox.addClass('dropbox-hover');
    },
    dragleave: function() {
        $dropbox.removeClass('dropbox-hover');
    },
    start: function() {
        $dropbox.removeClass('dropbox-hover');
        $dropbox.find('.progress').show().children('.progress-bar').css({width:0});
    },
    progress: function(data) {
        $dropbox.find('.progress .progress-bar').css({
            width: '' + data.percent + '%'
        });
    },
    success: function(data) {
        var old_url = $form.find('[name="attachment"]').val();
        $.post('ajax/path/to/uploaded', {
            'url': data.url,
            'old-url': old_url
        }, function(html){
            $dropbox.html(html);
        });
        $form.find('[name="attachment"]').val(data.url);
    },
    always: function() {
        $dropbox.removeClass('dragover');
        $dropbox.find('.progress').hide().children('.progress-bar').css({width:0});
    }
});
```
