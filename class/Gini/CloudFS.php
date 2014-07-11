<?php

namespace Gini 
{

    trait CloudFSTrait
    {
        /**
            * @brief 获取rpc实例
         */
        private static $_RPC = [];
        public function getRPC($type)
        {
            if (!self::$_RPC[$type]) {
                try {
                    $api = \Gini\Config::get($type . '.url');
                    $client_id = \Gini\Config::get($type . '.client_id');
                    $client_secret = \Gini\Config::get($type . '.client_secret');
                    $rpc = \Gini\IoC::construct('\Gini\RPC', $api, $type);
                    $rpc->authorize($client_id, $client_secret);
                    self::$_RPC[$type] = $rpc;
                } catch (\Gini\RPC\Exception $e) {
                    // rpc->authorize调用出现错误
                }
            }
            return self::$_RPC[$type];
        }
    }

    class CloudFS
    {

        use CloudFSTrait;

        private $_cloud;
        private $_config;
        private $_bucket;
        private $_has_access = null;
        /**
            * @brief 云服务器代理初始化
            *
            * @param $type 云的类型，如：qiniu、amazon S3；如果没有，则从配置文件获取默认值
            *
            * @return 
         */
        public function __construct($type=null)
        {
            $config = \Gini\Config::get('cloudfs');
            $this->_cloud = $type ?: $config['default'];
            $config = $config[$this->_cloud];
            $this->_bucket = $config['bucket'];
        }

        public function __call($method, $params=[])
        {
            $action = 'upload';
            if (is_null($this->_has_access)) {
                $this->_has_access = \Gini\Event::trigger("cloudfs.is_allowed_to[$action]", $this, $action);
            }
            $hasAccess = $this->_has_access;

            if (!$hasAccess) return;

            $bucket = $this->_bucket;
            $className = "\\Gini\\CloudFS\\{$this->_cloud}";
            $iCloud = \Gini\IoC::construct($className);
            $iCloud->setBucket($bucket);
            if (method_exists($iCloud, $method)) {
                return call_user_func_array(array($iCloud, $method), $params);
            }
        }
    }
}
