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
        $root = $options['root'] ?: APP_PATH.'/'.DATA_DIR.'/cloudfs';

        return $root.'/'.$filename;
    }

    private function _uploadMe($file)
    {
        $error = $file['error'];
        if ($error) {
            return $error;
        }

        $tmp = $file['tmp_name'];
        $size = $file['size'];

        $filename = \Gini\Util::randPassword().microtime();
        $filename = ($options['prefix'] ?: '').sha1(\Gini\Util::randPassword().microtime()).($ext ? '.'.$ext : '');

        return [
            'name' => $name,
            'type' => $ext,
            'size' => $size,
            'filename' => $filename,
            'file' => $this->_getFilePath($filename),
            'tmp' => $tmp,
        ];
    }

    public function upload(array $file)
    {
        $config = $this->_config;
        $options = $config['options'];

        $data = [];

        try {

            if ($file['error']) {
                throw new \Gini\CloudFS\Exception('File Type Error!');
            }

            $name = $file['name'];
            $ext = pathinfo($name, PATHINFO_EXTENSION);

            $types = (array) $options['types'];
            if (count($types) > 0 && !in_array($ext, $types)) {
                throw new \Gini\CloudFS\Exception('Illegal File Types');
            }

            $filename = ($options['prefix'] ?: '')
                . sha1(\Gini\Util::randPassword().microtime()).($ext ? '.'.$ext : '');
            $filepath = $this->_getFilePath($filename);
            \Gini\File::ensureDir(dirname($filepath));
            move_uploaded_file($file['tmp_name'], $filepath);
            $data['key'] = $filename;

            \Gini\Logger::of('cloudfs')->info('CloudFS/LocalFS uploaded {file} to {path}', ['file'=> $name, 'path' => $filepath ]);
        } catch (\Gini\CloudFS\Exception $e) {
            $message = $e->getMessage();
            \Gini\Logger::of('cloudfs')->error('CloudFS/LocalFS got error "{error}" on uploading {file}', ['error'=>$message, 'file'=>$file['error'] ?: $file['name']]);
            $data['error'] = $message;
        }

        return $data;
    }

    public function config(array $file)
    {
        $config = $this->_config;
        return [
            'url' => 'ajax/cloudfs/local/upload/'.$config['@name'],
        ];
    }

    public function _getUrl($filename)
    {
        return $filename;
    }

    public function callback(array $data)
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

    public function safeUrl($url)
    {
        $config = (array) $this->_config;
        $options = (array) $config['options'];
        return URL($options['url'] ?: 'cloudfs/local', ['f'=>$url]);
    }

    public function delete($url)
    {
        if (!$url) {
            return;
        }

        $key = ltrim(parse_url($url, PHP_URL_PATH), '/');
        // 检查路径合法性, 避免误删除
        if (strncmp($key, './', 2) == 0 || strncmp($key, '../', 3) == 0) {
            return;
        }

        $path = $this->_getFilePath($key);
        if (file_exists($path)) {
            \Gini\File::delete($path);
        }
        return false;
    }

    public function fetch($url, $file) {
        // localfs 暂时不支持抓取
        return;
    }
}
