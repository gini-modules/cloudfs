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
                return T("Filetype is not allowed!");
            }
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $filename = md5($tmp.time()) . ($ext ? '.' . $ext : '');
        $new = $this->getLocale() . '/' . $filename;

        return [
            'name'=> $name,
            'type'=> $type,
            'size'=> $size,
            'filename'=> $filename,
            'file'=> $new,
            'tmp'=> $tmp
        ];
    }

    public function upload($file)
    {
        $config = $this->_config;
        $callbacks = $config['callbacks'];
        $callback = $callbacks['upload'];
        $res = $this->_uploadMe($file);

        $data = [];
        if (!$callback || !is_callable($callback)) {
            $result = $res;
        }
        else {
            $result = call_user_func($callback, $res);
        }
        if (!is_array($reuslt)) {
            $data['error'] = $result;
        }
        else {
            move_uploaded_file($res['tmp'], $res['file']);
            $data = $result;
        }
        if (!isset($data['key'])) {
            $data['key'] = $res['filename'];
        }
        return $data;
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
        if ($data['error']) {
            return [
                'error'=> $data['error']
            ];
        }
        if ($data['key']) {
            return $this->getImageURL($data['key']);
        }
        return [];
    }

}
