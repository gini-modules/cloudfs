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

    class Server
    {
        // 允许驱动暴露出来让外部调用专门的函数
        public $driver;

        /**
         * @brief 云服务器代理初始化
         *
         * @param $type 云的类型，如：qiniu、amazon S3；如果没有，则从配置文件获取默认值
         *
         * @return
         */
        public function __construct($name = null)
        {
            $confs = \Gini\Config::get('cloudfs.server');
            $name = $name ?: $confs['default'];

            assert(isset($confs[$name]));
            $conf = $confs[$name];

            assert(isset($conf['driver']));
            $driver = $conf['driver'];
            $conf['@name'] = $name;

            $class = "\\Gini\\CloudFS\\Driver\\{$driver}";
            assert(class_exists($class));
            $this->driver = \Gini\IoC::construct($class, $conf);
        }

        public function config(array $data)
        {
            return $this->driver->config($data);
        }

        public function upload(array $data)
        {
            return $this->driver->upload($data);
        }

        public function callback(array $data)
        {
            return $this->driver->callback($data);
        }

        public function safeUrl($url)
        {
            return $this->driver->safeUrl($url);
        }

        public function delete($url)
        {
            return $this->driver->delete($url);
        }

        public function fetch($url, $file) {
            return $this->driver->fetch($url, $file);
        }

        public static function of($name)
        {
            return \Gini\IoC::construct('\Gini\CloudFS\Server', $name);
        }
    }
}
