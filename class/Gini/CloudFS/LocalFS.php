<?php

namespace Gini\CloudFS;

class LocalFS extends \Gini\CloudFS\Cloud
{
    private $_config = [];
    private $_client;
    public function __construct($client, $config)
    {
        $this->_client = $client;
        $this->_config = $config;
    }

    public function getLocale()
    {
        $config = $this->_config;
        $options = $config['options'];
        $root = $options['root'];
        $path = APP_PATH . '/';
        $locale = $root ? (strpos($root, '/')===0 ? $root : $path . $root) : $path . 'data/cloudfs/localfs';
        return $locale;
    }

    private function _uploadMe($file)
    {
        $name = $file['name'];
        $type = $file['type'];
        $tmp = $file['tmp_name'];
        $size = $file['size'];
        $error = $file['error'];

        if ($error) return $error;

        $config = $this->_config;
        $options = $config['options'];
        $types = $options['types'];
        if (!empty($types)) {
            if (!in_array($type, $types)) {
                return "Type {$type} is not allowed!";
            }
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $filename = md5($tmp.time()) . ($ext ? '.' . $ext : '');
        $new = $this->getLocale() . '/' . $filename;
        if (!move_uploaded_file($tmp, $new)) {
            return 'Upload Failed!';
        }
        return [
            'name'=> $name,
            'type'=> $type,
            'size'=> $size,
            'filename'=> $filename,
            'file'=> $new
        ];
    }

    public function upload($file)
    {
        $config = $this->_config;
        $callbacks = $config['callbacks'];
        $callback = $callbacks['upload'];
        $res = $this->_uploadMe($file);
        if (!is_array($res)) return;
        if (!$callback || !is_callable($callback)) {
            return ['key'=>$res['filename']];
        }
        $result = call_user_func($callback, $res);
        if (!is_array($result)) {
            $result = [
                'data'=> $result
            ];
        }
        if (!isset($result['key'])) {
            $result['key'] = $res['filename'];
        }
        return $result;
    }

    public function getUploadConfig()
    {
        $config = $this->_config;
        $options = $config['options'];
        $data = [];
        if ($options['url']) {
            $data['url'] = $options['url'];
        }
        else {
            $data['url'] = '/ajax/cloudfs/localfs/upload/' . $this->_client;
        }
        return $data;
    }

    public function getImageURL($filename) 
    {
        $config = $this->_config;
        $callbacks = $config['callbacks'];
        $callback = $callbacks['get_file_info'];
        if (!$callback || !is_callable($callback)) return $filename;
        $result = call_user_func($callback, $filename);
        return $result;
    }

    public function parseData(array $data=[]) 
    {
        $image = $this->getImageURL($data['key']);
        if (!is_array($image)) {
            $image = [$image];
        }
        return $image;
    }

}
