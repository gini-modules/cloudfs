<?php
/**
* @file CloudFS.php
* @brief 客户端有效性验证
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

namespace Gini\Controller\API;

class CloudFS extends \Gini\Controller\API
{
    public function actionAuthorize($server, $clientId, $clientSecret)
    {
        $config = (array)\Gini\Config::get('cloudfs.server');
        if (!isset($config[$server])) return false;
        if (!isset($config[$server]['clients'][$clientId])) return false;
        if ($config[$server]['clients'][$clientId]!==$clientSecret) return false;
        return true;
    }

    public function actionGetClientConfig($server)
    {
        $config = (array)\Gini\Config::get('cloudfs.server');
        if (!isset($config[$server])) return false;
        $data = $config[$server]['client_options'];
        return $data;
    }
}
