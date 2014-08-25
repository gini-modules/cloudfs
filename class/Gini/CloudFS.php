<?php
/**
* @file CloudFS.php
* @brief 云端调用的入口
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

namespace Gini 
{

    trait CloudFSTrait
    {
        /**
            * @brief 获取rpc实例
         */
        private static $_RPC = [];
        public function getRPC($type, $config=null)
        {
            if (!self::$_RPC[$type] && isset($config) && is_array($config)) {
                try {
                    $api = $config['url'];
                    $client_id = $config['client_id'];
                    $client_secret = $config['client_secret'];
                    $rpc = \Gini\IoC::construct('\Gini\RPC', $api, $type);
                    $bool = $rpc->authorize($config['server'], $client_id, $client_secret);
                    if ($bool) self::$_RPC[$type] = $rpc;
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

        private $_client;
        private $_driver;
        /**
            * @brief 云服务器代理初始化
            *
            * @param $type 云的类型，如：qiniu、amazon S3；如果没有，则从配置文件获取默认值
            *
            * @return 
         */
        public function __construct($type=null)
        {
            $config = \Gini\Config::get('cloudfs.client');
            $clientKey = $type ?: $config['default'];
            if (isset($config[$clientKey])) {
                $this->_client = $clientKey;
            }
        }

        public function __call($method, $params=[])
        {
            if (!$this->_client) return;

            $config = \Gini\Config::get('cloudfs.client');
            $configClient = $config[$this->_client];
            $driver = $configClient['driver'];

            if (!$driver) return;
            $className = "\\Gini\\CloudFS\\{$driver}";
            $iCloud = \Gini\IoC::construct($className, $configClient);
            if (method_exists($iCloud, $method)) {
                // action的取值：upload/getImageURL/getThumbURL/getUploadConfig
                $action = strtolower($method);
                $hasAccess = \Gini\Event::trigger("cloudfs.is_allowed_to[$action]", $this, $action);
                // 除非明确返回false，否走都认为用户是有权限的
                if (false===$hasAccess) return;

                return call_user_func_array(array($iCloud, $method), $params);
            }
        }
    }
}
