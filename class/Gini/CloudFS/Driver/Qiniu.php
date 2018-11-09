<?php

/**
 * @file Qiniu.php
 * @brief 七牛云前代理
 *
 * @author PiHiZi
 *
 * @version 0.1.0
 * @date 2014-07-11
 */

namespace Gini\CloudFS\Driver;

class Qiniu implements \Gini\CloudFS\Driver
{
    private $_config = [];

    public function __construct($config)
    {
        $this->_config = $config;
    }

    private function _getFilename($file)
    {
        $options = $this->_config['options'];

        if (isset($options['use_real_file_name']) && $options['use_real_file_name']) {
            $name = $file;
        } else {
            $host = $_SERVER['HTTP_HOST'] ?: $_SERVER['SERVER_NAME'];
            $filename = \Gini\Util::randPassword().microtime();
            $name = ($options['prefix'] ?: '').sha1($host.$filename).'.'.pathinfo($file, PATHINFO_EXTENSION);
        }

        return $name;
    }

    private function _getToken($filename, $cbkURL = null, $cbkBody = null)
    {
        $options = $this->_config['options'];
        $bucket = $options['bucket'];

        $accessKey = $options['accessKey'];
        $secretKey = $options['secretKey'];

        $auth = new \Qiniu\Auth($accessKey, $secretKey);

        $opts = [];
        if (isset($options['callback_body'])) {
            $opts['callbackBody'] = $cbkURL ?: $options['callback_url'];
        }
        if (isset($options['callback_url'])) {
            $opts['callbackUrl'] = $cbkBody ?: $options['callback_body'];
        }

        return $auth->uploadToken($bucket, null, 3600, $opts);
    }

    private function _filterResult($data, $error)
    {
        $result = false;
        $callbacks = (array) $this->_config['callbacks'];
        if ($error) {
            if (isset($callbacks['fail'])) {
                $result = call_user_func($callbacks['fail'], $error);
            }
        } else {
            if (isset($callbacks['success'])) {
                $result = call_user_func($callbacks['success'], $data);
            } else {
                $result = $data;
            }
        }

        if (isset($callbacks['always'])) {
            $result = call_user_func($callbacks['always'], [$data, $error]);
        }

        return $result;
    }

    public function isFromQiniuServer()
    {
        $authstr = $_SERVER['HTTP_AUTHORIZATION'];
        if (strpos($authstr, 'QBox ') != 0) {
            return false;
        }

        $auth = explode(':', substr($authstr, 5));
        if (sizeof($auth) != 2) {
            return false;
        }

        $data = $_SERVER['REQUEST_URI']."\n".file_get_contents('php://input');
        list($iAccessKey, $encodedData) = $auth;

        $options = $this->_config['options'];
        $bucket = $options['bucket'];

        $accessKey = $options['accessKey'];
        $secretKey = $options['secretKey'];

        if ($iAccessKey !== $accessKey) {
            return false;
        }

        $myEData = str_replace(['+', '/'], ['-', '_'], base64_encode(hash_hmac('sha1', $data, $secretKey, true)));
        if ($myEData !== $encodedData) {
            return false;
        }

        return true;
    }

    public function runServerCallback(array $data)
    {
        $error = ($data['key'] && $data['hash']) ? false : ['code' => 0, 'error' => 'Response error from qiniu server.'];
        $result = $this->_filterResult($data, $error);

        return $result;
    }

    private function _getUrl($file)
    {
        $options = $this->_config['options'];
        $bucket = $options['bucket'];

        $domain = $options['domain'] ?: $bucket.'.qiniudn.com';
        $url = "http://{$domain}/{$file}";

        return $url;
    }

    public function upload(array $file)
    {
        $realFilename = $file['tmp_name'];
        if (!file_exists($realFilename)) {
            return false;
        }

        $filename = $this->_getFilename($file['name']);
        $token = $this->_getToken($filename);

        $upManager = new \Qiniu\Storage\UploadManager();
        list($ret, $err) = $upManager->putFile($token, $filename, $realFilename);

        return $this->_filterResult($ret, $err);
    }

    public function config(array $file)
    {
        $config = $this->_config;
        $options = $config['options'];

        $data = [];
        $params = [];

        if (isset($options['callback_url'])) {
            $params['x:callbackUrl'] = $options['callback_url'];
        }

        if (isset($options['callback_body'])) {
            $params['x:callbackBody'] = $options['callback_body'];
        }

        $data['url'] = 'http://up.qiniu.com';

        $filename = $this->_getFilename($file['name']);
        $token = $this->_getToken($filename);

        $params['key'] = $filename;
        $params['token'] = $token;

        $data['params'] = $params;

        return $data;
    }

    public function callback(array $data)
    {
        if (!isset($data['key'])) {
            return;
        }

        return [
            'url' => $this->_getUrl($data['key']),
        ];
    }

    public function delete($url)
    {
        if (!$url) {
            return;
        }

        $options = $this->_config['options'];
        $bucket = $options['bucket'];

        $accessKey = $options['accessKey'];
        $secretKey = $options['secretKey'];

        $auth = new \Qiniu\Auth($accessKey, $secretKey);

        $key = ltrim(parse_url($url, PHP_URL_PATH), '/');
        $bucketManager = new \Qiniu\Storage\BucketManager($auth);

        $ret = $bucketManager->delete($bucket, $key);

        return ($ret === null || !@$ret->error);
    }

    public function safeUrl($url)
    {
        $options = $this->_config['options'];
        $bucket = $options['bucket'];

        $accessKey = $options['accessKey'];
        $secretKey = $options['secretKey'];

        $auth = new \Qiniu\Auth($accessKey, $secretKey);

        return $auth->privateDownloadUrl($url);
    }

    public function fetch($url, $file) {
        if (!$url) {
            return;
        }

        $options = $this->_config['options'];
        $bucket = $options['bucket'];

        $accessKey = $options['accessKey'];
        $secretKey = $options['secretKey'];

        $auth = new \Qiniu\Auth($accessKey, $secretKey);

        $key = $this->_getFilename($file);
        $bucketManager = new \Qiniu\Storage\BucketManager($auth);

        list($ret, $err) = $bucketManager->fetch($url, $bucket, $key);
        if ($err) return false;

        return $this->_getUrl($ret['key']);
    }
}
