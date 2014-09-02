<?php
/**
* @file Qiniu.php
* @brief 七牛
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

namespace Gini\Controller\API\CloudFS;

require_once(APP_PATH.'/vendor/qiniu/php-sdk/qiniu/rs.php');
require_once(APP_PATH.'/vendor/qiniu/php-sdk/qiniu/io.php');
require_once(APP_PATH.'/vendor/qiniu/php-sdk/qiniu/fop.php');

class Qiniu extends \Gini\Controller\API
{
    private function getOptions()
    {
        $sess = $_SESSION['cloudfs.rpc.qiniu.config'];
        return $sess['options'];
    }

    private function getBucketName()
    {
        $sess = $_SESSION['cloudfs.rpc.qiniu.config'];
        return $sess['bucket'];
    }

    public function actionInit($serverKey)
    {
        $config = \Gini\Config::get('cloudfs.server');
        if (!isset($config[$serverKey])) {
            $config = $config[$config['default']];
        }
        else {
            $config = $config[$serverKey];
        }
        $options = $config['options'];
        $_SESSION['cloudfs.rpc.qiniu.config'] = [
            'bucket'=> $options['bucket']
            ,'options'=> $options
        ];
    }

    // 暂时不允许客户端自定义filename
    public function actionGetURI($host, $filename='')
    {
        $filename = \Gini\Util::randPassword() . microtime();
        $result = md5($host.$filename).'.jpg';
        return $result;
    }

    public function actionGetKeys($params)
    {
        require_once(APP_PATH.'/vendor/qiniu/php-sdk/qiniu/rs.php');

        $bucket = $this->getBucketName();
        $config = $this->getOptions();

        if (!$config || !$bucket) return;

        $config = [ $bucket, $config['accessKey'], $config['secretKey'] ];
        list($bucket, $accessKey, $secretKey) = $config;

        \Qiniu_SetKeys($accessKey, $secretKey);

        $filename = $params['file'];
        $filename = $filename ? "{$bucket}:{$filename}" : $bucket;
        $putPolicy = new \Qiniu_RS_PutPolicy($filename);

        if (isset($params['callback_body'])) {
            $putPolicy->CallbackBody = $params['callback_body'];
        }
        if (isset($params['callback_url'])) {
            $putPolicy->CallbackUrl = $params['callback_url'];
        }

        $token = $putPolicy->Token(null);
        return $token;
    }

    public function actionIsFromQiniuServer($data, $iAccessKey, $encodedData)
    {
        $bucket = $this->getBucketName();
        $config = $this->getOptions();

        if (!$config || !$bucket) return;

        $config = [ $bucket, $config['accessKey'], $config['secretKey'] ];
        list($bucket, $accessKey, $secretKey) = $config;
        
        if ($iAccessKey!==$accessKey) return false;

        $myEData = str_replace(['+', '/'], ['-', '_'], base64_encode(hash_hmac('sha1', $data, $secretKey, true)));
        if ($myEData !== $encodedData) {
            return false;
        }
        return true;
    }

    public function actionGetImageURL($file)
    {
        $bucket = $this->getBucketName();
        if (!$bucket) return;
        $imgViewUrl = \Qiniu_RS_MakeBaseUrl("{$bucket}.qiniudn.com", $file);
        $imgViewUrl .= '?' . time();
        return $imgViewUrl;
    }
}
