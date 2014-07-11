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
    public function actionAuthorize($clientId, $clientSecret)
    {
        return true;
    }
}
