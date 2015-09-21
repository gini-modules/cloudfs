<?php

/**
 * @file Cloud.php
 * @brief 抽象类，定义了各个cloud至少应该实现的方法
 *
 * @author PiHiZi
 *
 * @version 0.1.0
 * @date 2014-07-11
 */
namespace Gini\CloudFS;

abstract class Driver
{
    /**
     * @brief 获取rpc实例
     */
    private static $_RPC = [];
    public function getRPC($type, $config = null)
    {
        if (!self::$_RPC[$type] && isset($config) && is_array($config)) {
            try {
                $api = $config['url'];
                $client_id = $config['client_id'];
                $client_secret = $config['client_secret'];

                $rpc = \Gini\IoC::construct('\Gini\RPC', $api, $type);
                self::$_RPC[$type] = $rpc;

                $token = $rpc->authorize($config['server'], $client_id, $client_secret);
                if (!$token) {
                    throw new \Gini\RPC\Exception('Access Denied!', 401);
                }
            } catch (\Gini\RPC\Exception $e) {
                \Gini\Logger::of('cloudfs')->error('Cloud::getRPC {message}[{code}]', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
            }
        }

        return self::$_RPC[$type];
    }

    abstract public function upload($file);

    abstract public function getImageURL($filename);

    abstract public function getUploadConfig($file);
}
