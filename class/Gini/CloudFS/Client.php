<?php

/**
 * @file CloudFS.php
 * @brief 云端调用的入口
 *
 * @author PiHiZi
 *
 * @version 0.1.0
 * @date 2014-07-11
 */
namespace Gini\CloudFS
{

    class Client
    {
        private $_client;
        private $_driver;
        /**
         * @brief 云服务器代理初始化
         *
         * @param $type 云的类型，如：qiniu、amazon S3；如果没有，则从配置文件获取默认值
         *
         * @return
         */
        public function __construct($type = null)
        {
            $config = \Gini\Config::get('cloudfs.client');
            $clientKey = $type ?: $config['default'];
            if (isset($config[$clientKey])) {
                $this->_client = $clientKey;
            }
        }

        public function __call($method, $params = [])
        {
            if (!$this->_client) {
                return;
            }

            $config = \Gini\Config::get('cloudfs.client');
            $configClient = $config[$this->_client];
            $driver = $configClient['driver'];

            if (!$driver) {
                return;
            }

            $className = "\\Gini\\CloudFS\\Driver\\{$driver}";
            $iCloud = \Gini\IoC::construct($className, $this->_client, $configClient);
            if (method_exists($iCloud, $method)) {
                // action的取值：upload/getImageURL/getThumbURL/getUploadConfig
                $action = strtolower($method);
                $callbacks = $configClient['callbacks'];
                if (isset($callbacks) && is_array($callbacks) && isset($callbacks['prepare'])) {
                    $hasAccess = call_user_func($callbacks['prepare'], $action, $params);
                    // 除非明确返回false，否走都认为用户是有权限的
                    if (false === $hasAccess) {
                        return;
                    }
                }

                return call_user_func_array(array($iCloud, $method), $params);
            }
        }

        public static function authorize($server, $clientId, $clientSecret)
        {
            $config = (array) \Gini\Config::get('cloudfs.server');
            if (!isset($config[$server])) {
                return false;
            }
            if (!isset($config[$server]['clients'][$clientId])) {
                return false;
            }
            if ($config[$server]['clients'][$clientId] !== $clientSecret) {
                return false;
            }
            $_SESSION['cloudfs.client'] = $clientId;

            return true;
        }

        public static function isAuthorized()
        {
            return !!$_SESSION['cloudfs.client'];
        }
    }
}
