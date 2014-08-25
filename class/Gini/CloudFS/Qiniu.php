<?php
/**
* @file Qiniu.php
* @brief 七牛云前代理
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

namespace Gini\CloudFS;

require_once(APP_PATH.'/vendor/qiniu/php-sdk/qiniu/rs.php');
require_once(APP_PATH.'/vendor/qiniu/php-sdk/qiniu/io.php');
require_once(APP_PATH.'/vendor/qiniu/php-sdk/qiniu/fop.php');

class Qiniu extends \Gini\CloudFS\Cloud
{
    const UPLOAD_DIRECT = 'direct';
    const UPLOAD_VIA_SERVER = 'via-server';

    private $_config = [];
    public function __construct($config)
    {
        $this->_config = $config;
        $this->getRPC('cloudfs', $this->_config['rpc'])->qiniu->init($this->_config['rpc']['server']);
    }

    public function getUploadConfig()
    {
        $config = $this->_config;
        $options = $config['options'];
        $data = [
            'url'=> $options['url']
            ,'callback'=> $options['callback']
            ,'params'=> (array)$options['params']
        ];
        if ($options['mode']===self::UPLOAD_DIRECT) {
            $filename = $this->_getFilename();
            $keys = $this->_getToken($filename);

            $data['params']['key'] = $filename;
            $data['params']['token'] = $keys;
        }

        return $data;
    }

    private function _getFilename()
    {
        $host = $_SERVER['HTTP_HOST'] ?: $_SERVER['SERVER_NAME'];
        $filename = $this->getRPC('cloudfs')->qiniu->getURI($host);
        return $filename;
    }

    private function _getToken($filename, $cbkURL=null, $cbkBody=null)
    {
        $options = $this->_config['options'];
        $bucket = $options['bucket'];
        if (!$bucket) return;
        $token = $this->getRPC('cloudfs')->qiniu->getKeys([
            'bucket'=> $bucket
            ,'method'=> 'upload'
            ,'file'=> $filename
            ,'callbackUrl'=> $cbkURL ?: $options['params']['x:callbackUrl']
            ,'callbackBody'=> $cbkBody ?: $options['params']['x:callbackBody']
        ]);
        return $token;
    }

    public function upload($file)
    {
        $result = false;

        $file = $file['tmp_name'];
        if (!$file) return $result;
        
        $filename = $this->_getFilename();
        $token = $this->_getToken($filename);
        $content = file_get_contents($file);
        list($ret, $err) = \Qiniu_Put($token, $filename, $content, null);

        $callbacks = (array)$this->_config['callbacks'];
        if ($err) {
            if (isset($callbacks['fail'])) {
                $result = call_user_func($callbacks['fail'], $err);
            }
        }
        else {
            if (isset($callbacks['success'])) {
                $result = call_user_func($callbacks['success'], $ret);
            }
            else {
                $result = $ret;
            }
        }
        
        if (isset($callbacks['always'])) {
            $result = call_user_func($callbacks['always'], [$ret, $err]);
        }

        return $result;
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

    public function runServerCallback($data)
    {
        $result = \Gini\Event::trigger("cloudfs.qiniu_callback", $this, $data);
        return $result;
    }

    public function getImageURL($filename)
    {
        $options = $this->_config['options'];
        $bucket = $options['bucket'];
        $imgViewUrl = \Qiniu_RS_MakeBaseUrl("{$bucket}.qiniudn.com", $filename);
        $imgViewUrl .= '?' . time();
        return $imgViewUrl;
    }

    /*
    public function getThumbURL($filename, $width=0, $height=0)
    {
        $options = $this->_config['options'];
        $bucket = $options['bucket'];
        $imgViewUrl = \Qiniu_RS_MakeBaseUrl("{$bucket}.qiniudn.com", $filename);
        $imgViewUrl .= '?' . time();
        $opts = [
            1   // mode
            ,'w', $width?:$this->_config['image-max-width']
            ,'h', $height?:$this->_config['image-max-height']
        ];
        $imgViewUrl .= '&imageView/' . implode('/', $opts);
        return $imgViewUrl;
    }
     */

    /*
    private $_options;
    private $_bucket;
    private $_config;
    public function __construct($client)
    {
        $config = \Gini\Config::get('cloudfs.client');
        $config = $config[$client];
        $this->_bucket = $config['bucket'];
        $this->_options = $config['options'];
        unset($config['options']);
        $this->_config = $config;
        $this->getRPC('cloudfs')->qiniu->init($this->_bucket);
    }

    public function getImageURL($filename)
    {
        $bucket = $this->_bucket;
        $imgViewUrl = \Qiniu_RS_MakeBaseUrl("{$bucket}.qiniudn.com", $filename);
        $imgViewUrl .= '?' . time();
        return $imgViewUrl;
    }

    public function getThumbURL($filename, $width=0, $height=0)
    {
        $bucket = $this->_bucket;
        $imgViewUrl = \Qiniu_RS_MakeBaseUrl("{$bucket}.qiniudn.com", $filename);
        $imgViewUrl .= '?' . time();
        $opts = [
            1   // mode
            ,'w', $width?:$this->_config['image-max-width']
            ,'h', $height?:$this->_config['image-max-height']
        ];
        $imgViewUrl .= '&imageView/' . implode('/', $opts);
        return $imgViewUrl;
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
            ,'callbackUrl'=> $cbkURL ?: $this->_options['params']['x:callbackUrl']
            ,'callbackBody'=> $cbkBody ?: $this->_options['params']['x:callbackBody']
        ]);
        return $token;
    }

    public function upload($file)
    {
        $result = false;

        $file = $file['tmp_name'];
        if (!$file) return $result;
        
        $filename = $this->_getFilename();
        $content = file_get_contents($file);
        $token = $this->_getToken($filename);
        list($ret, $err) = \Qiniu_Put($token, $filename, $content, null);

        $result = $err ? $result : $ret;
        return $result;
    }

    public function getUploadConfig()
    {
        $config = $this->_options;
        $config['params'] = $config['params'] ?: [];
        // 如果上传到的非当前host，则认为是直接云传，七牛可以提供token
        $myHost = $_SERVER['HTTP_HOST'];
        $uploadTo = parse_url($config['url']);
        $uHost = $uploadTo['host'];
        if ($uHost && $uHost!==$myHost) {
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

        $result = $this->getRPC('cloudfs.qiniu')->qiniu->isFromQiniuServer($data, $auth[0], $auth[1]);
        return !!$result;
    }

    public function runServerCallback($data)
    {
        $result = \Gini\Event::trigger("cloudfs.qiniu_callback", $this, $data);
        return $result;
    }
     */
}
