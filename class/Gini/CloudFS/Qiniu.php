<?php
/**
* @file Qiniu.php
* @brief Qiniu php-sdk的代理层
* @author Hongjie Zhu
* @version 1.0.0
* @date 2014-05-13
 */

namespace Gini\CloudFS;

require_once(__DIR__.'/../../../vendor/qiniu/php-sdk/qiniu/rs.php');
require_once(__DIR__.'/../../../vendor/qiniu/php-sdk/qiniu/io.php');
require_once(__DIR__.'/../../../vendor/qiniu/php-sdk/qiniu/fop.php');

class Qiniu extends \Gini\CloudFS\Cloud
{
    const CLOUD_NAME = 'qiniu';

    private $_config;
    private $_bucket;
    public function __construct()
    {
        $cloud = self::CLOUD_NAME;
        $config = \Gini\Config::get('cloudfs.'.self::CLOUD_NAME);
        $this->_bucket = $config['bucket'];
        $this->_config = $config['client'];
    }

    public function getImageURL($filename)
    {
    }

    public function getThumbURL($filename, $width=0, $height=0)
    {
    }

    private function _getFilename()
    {
        $host = $_SERVER['HTTP_HOST'] ?: $_SERVER['SERVER_NAME'];
        $filename = $this->getRPC('cloudfs')->qiniu->getURI($host);
        return $filename;
    }

    private function _getToken($filename, $cbkURL=null, $cbkBody=null)
    {
        $bucket = $this->_bucket;
        $token = $this->getRPC('cloudfs')->qiniu->getKeys([
            'bucket'=> $bucket
            ,'method'=> 'upload'
            ,'file'=> $filename
            ,'callbackUrl'=> $cbkURL
            ,'callbackBody'=> $cbkBody
        ]);
        return $token;
    }

    public function upload($file)
    {
        $result = false;

        $file = $file['tmp_name'];
        if ($file) return $result;
        
        $filename = $this->_getFilename();
        $content = file_get_contents($file);
        $token = $this->_getToken($filename);
        list($ret, $err) = \Qiniu_Put($token, $filename, $content, null);

        $result = $err ? $result : $filename;
        return $result;
    }

    public function getUploadConfig($type='cloud')
    {
        $type = $type ?: 'cloud';
        $config = $this->_config[$type];
        if (!$config) return;

        $config['params'] = $config['params'] ?: [];

        $tokenType = $config['tokenType'];
        unset($config['tokenType']);
        if ($tokenType=='cloud') {
            
            $filename = $this->_getFilename();
            $keys = $this->_getToken($filename, $config['params']['callbackUrl'], $config['params']['callbackBody']);

            $config['params']['key'] = $filename;
            $config['params']['token'] = $keys;
        }

        return $config;
    }

    public function isFromQiniuServer()
    {
        $authstr = $_SERVER['HTTP_AUTHORIZATION'];
        if (strpos($authstr, 'QBox ')!=0) {
            return false;
        }
        $auth = explode(':', substr($authstr, 5));
        if (sizeof($auth)!=2) return false;
        $data = $_SERVER['REQUEST_URI'] . "\n" . file_get_contents('php://input');

        $result = $this->getRPC('cloudfs')->qiniu->isFromQiniuServer($data, $auth[0], $auth[1]);
        return !!$result;
    }

}
