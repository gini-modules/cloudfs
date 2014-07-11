
云前的一个封装

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
            # 在没有指定选择的cloud时，默认使用的配置，在client角色的站点配置
            default: qiniu
            # 各类cloud的配置信息，如qiniu
            qiniu:
                # 当前云所使用的bucket
                bucket: cloudfs
                # client在担任client的服务器需要指定client项
                client:
                    # 用户自定义当前client支持的文件上传方式
                    # cloud：直接在浏览器端直接上传到云端
                    cloud:
                        # 上传文件的地址
                        url: http://up.qiniu.com/
                        # 为浏览器颁发token的方式
                        # cloud：由server产生
                        # 其他：有各个app自主控制
                        tokenType: cloud
                        # 自定义数据
                        # 数据传送给browser，browser在向云端上传文件时，将附带这些数据
                        params:
                            x\:callbackUrl: http://YOUR-DOMAIN/ajax/cloudfs/qiniu/callback
                            x\:callbackBody: key=$(key)&id=$(x:id)
                        # 上传文件之后，如果需要有其他的处理，可以指定callback项
                        # browser以post方式将上传成功的返回结构发送给该callback，并取得返回值
                        #callback: YOUR-CALLBACK-URL
                    # local: 通过cloudfs将文件中转上传
                    local:
                        url: /ajax/cloudfs/qiniu/upload
                        #callback: YOUR-CALLBACK-URL
                # 在担任server的服务器需要指定server项
                server:
                    accessKey: XlvRPPcaj1hyvHPE_LkMCqfP9BAeaCn0b1OxPQSd
                    secretKey: MfezOYL7-rNaEyjyfhG1O4IqGWJysBW2BD3Ygh1p
            ...

    2.2. hooks.yml

            ---
            cloudfs.is_allowed_to[upload]:
            - callback:\Gini\....\XXX::CALLBACKNAME
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

4. 权限验证Callback: 如果没有callback，默认用户是没有权限的, 将无法进行任何操作
