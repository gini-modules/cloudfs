
云前的一个封装

### 安装

1. git clone https://github.com/pihizi/gini-cloudfs.git cloudfs

1. gini composer init

1. composer install

1. gini cache && gini config update

### 调用方法

1. 部署依赖关系: 在gini.json中声明依赖

        "dependencies": {
            ...
            "cloudfs": "*",
            ...
        }

2. 初始化配置文件

    2.1. cloudfs.yml

            ---
            # RPC 调用相关的参数
            # 在担任cliet角色的站点中需要配置，如mall-brand
            url: http://cloudfs.gapper.in/api
            client_id: 5a2fe5921a81b4c7fe1f8a32fe1c0268
            client_secret: 2e35f59e419d1cbc62ce24043aa0c229
            # client在担任client的服务器需要指定client项
            client:
                # 在没有指定选择的cloud时，默认使用的配置，在client角色的站点配置
                default: qiniu-client
                # 各类cloud的配置信息，如qiniu, abc
                qiniu-client:
                    # 使用的云接口: qiniu
                    driver: qiniu
                    # 当前云所使用的bucket
                    bucket: app-brand
                    # 图像裁剪，最大宽度
                    image-max-width: 150
                    # 图像裁剪，最大高度
                    image-max-height: 150
                    # 用户自定义当前client支持的文件上传方式
                    options:
                        # 上传文件的地址
                        #url: http://up.qiniu.com/
                        #也可以上传到本地
                        url: /ajax/cloudfs/qiniu/upload
                        #上传成功后的客户端回调函数
                        callback: /ajax/cloudfs/qiniu/parseData
                        # 自定义数据
                        # 数据传送给browser，browser在向云端上传文件时，将附带这些数据
                        # 系统占用了'cfs:'开头的自定义变量，请使用时注意
                        params:
                            #以下是七牛支持的自定义变量
                            #x:callbackUrl: http://YOUR-DOMAIN/ajax/cloudfs/qiniu/callback
                            # CloudFS定义的，用无状态的保持client name
                            #x:client: qiniu-client
                            #x:callbackBody: key=$(key)&client=$(x:client)

            # 在担任server的服务器需要指定server项
            server:
                qiniu-server-a:
                    driver: qiniu
                    # 除了driver和options为固定项外，其余项由DRIVER控制
                    # 比如：qiniu会有bucket，而ftp的方式可能就没有
                    bucket: app-brand
                    options:
                        #不同的cloud可能或有不同的配置
                        accessKey: XlvRPPcaj1hyvHPE_LkMCqfP9BAeaCn0b1OxPQSd
                        secretKey: MfezOYL7-rNaEyjyfhG1O4IqGWJysBW2BD3Ygh1p
            ...

    2.2. hooks.yml

            ---
            cloudfs.is_allowed_to[upload]:
            - callback:\Gini\....\XXX::CALLBACKNAME
            cloudfs.qiniu_callback:
            - callback:APP-CALLBACK::FUNCTION
            ...

3. 前端调用

        require(['cloudfs'], function(CloudFS) {
            var $cfs = new CloudFS('cloud', 'qiniu');
            // var $cfs = new CloudFS('cloud');
            // var $cfs = new CloudFS('local');
            // var $cfs = new CloudFS('cloud', 'aws');
            $cfs.upload(file, {
                'progress': function() {}
                ,'success': function() {}
                ,'error': function() {}
                ,'always': function() {}
            });
        });

4. 权限验证Callback(在hooks.yml中定义): 如果没有callback，只有明确返回false，表示没有权限
