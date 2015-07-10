<?php
/**
* @file Qiniu.php
* @brief 七牛
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

namespace Gini\Controller\API\CloudFS;

class Qiniu extends \Gini\Controller\API
{
    private function _getOptions()
    {
        $sess = $_SESSION['cloudfs.rpc.qiniu.config'];
        return $sess['options'];
    }

    private function _getBucketName()
    {
        $sess = $_SESSION['cloudfs.rpc.qiniu.config'];
        return $sess['bucket'];
    }

    public function actionInit($serverKey)
    {
        if (\Gini\CloudFS\Client::isAuthorized())
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

    public function actionGetToken($params)
    {
        $bucket = $this->_getBucketName();
        $config = $this->_getOptions();

        if (!$config || !$bucket) return;

        $config = [ $bucket, $config['accessKey'], $config['secretKey'] ];
        list($bucket, $accessKey, $secretKey) = $config;

        $auth = new \Qiniu\Auth($accessKey, $secretKey);

        $opts = [];
        if (isset($params['callback_body'])) {
            $opts['callbackBody'] = $params['callback_body'];
        }
        if (isset($params['callback_url'])) {
            $opts['callbackUrl'] = $params['callback_url'];
        }
        
        return $auth->uploadToken($bucket, null, 3600, $opts);
    }

    public function actionIsFromQiniuServer($data, $iAccessKey, $encodedData)
    {
        $bucket = $this->_getBucketName();
        $config = $this->_getOptions();

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
        $bucket = $this->_getBucketName();
        if (!$bucket) return;

        $bucket = $this->_getBucketName();
        $config = $this->_getOptions();

        $domain = $config['domain'] ?: $bucket.'.qiniudn.com';
        // $imgViewUrl = \Qiniu_RS_MakeBaseUrl($domain, $file);
        $imgViewUrl = "http://{$domain}/{$file}";
        return $imgViewUrl;
    }
}
