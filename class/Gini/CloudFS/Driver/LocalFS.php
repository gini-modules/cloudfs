<?php

namespace Gini\CloudFS\Driver;

class LocalFS implements \Gini\CloudFS\Driver
{
    private $_config = [];

    public function __construct($config)
    {
        $this->_config = $config;
    }

    private function _getFilePath($filename)
    {
        $config = $this->_config;
        $options = $config['options'];
        $root = $options['root'] ?: APP_PATH.'/'.DATA_DIR.'/cloudfs/localfs';

        return $root.'/'.$filename;
    }

    private function _uploadMe($file)
    {
        $error = $file['error'];
        if ($error) {
            return $error;
        }

        $name = $file['name'];
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        $tmp = $file['tmp_name'];
        $size = $file['size'];

        $config = $this->_config;
        $options = $config['options'];
        $types = (array) $options['types'];
        if (!in_array($ext, $types)) {
            throw new \Gini\CloudFS\Exception('FileType Error!', 1);
        }

        $filename = md5($tmp.time()).($ext ? '.'.$ext : '');

        return [
            'name' => $name,
            'type' => $ext,
            'size' => $size,
            'filename' => $filename,
            'file' => $this->_getFilePath($filename),
            'tmp' => $tmp,
        ];
    }

    public function upload($file)
    {
        $config = $this->_config;

        $callbacks = $config['callbacks'];
        $callback = $callbacks['upload'];
        try {
            $res = $this->_uploadMe($file);
        } catch (\Gini\CloudFS\Exception $e) {
            $error = $e;
        }

        $data = [];
        if (!$callback || !is_callable($callback)) {
            $result = $res;
        } else {
            $result = call_user_func($callback, $res, $error);
        }

        if (!is_array($result)) {
            $data['error'] = $result;
        } else {
            move_uploaded_file($res['tmp'], $res['file']);
            $data = $result;
        }

        if (!isset($data['key']) && is_array($res) && isset($res['filename'])) {
            $data['key'] = $res['filename'];
        }

        return $data;
    }

    public function config($file = null)
    {
        $options = $this->_config['options'];

        $data = [
            'url' => $options['url'] ?: '/ajax/cloudfs/localfs/upload',
            'params' => [
                'server' => $this->_config['@name'],
            ],
        ];

        return $data;
    }

    public function _getUrl($filename)
    {
        $config = $this->_config;
        $callbacks = $config['callbacks'];
        $callback = $callbacks['get_file_info'];
        if (!$callback || !is_callable($callback)) {
            return $filename;
        }
        $result = call_user_func($callback, $filename);

        return $result;
    }

    public function callback(array $data = [])
    {
        if ($data['error']) {
            return [
                'error' => $data['error'],
            ];
        }
        if ($data['key']) {
            return [
                'url' => $this->_getUrl($data['key']),
            ];
        }

        return [];
    }
}
