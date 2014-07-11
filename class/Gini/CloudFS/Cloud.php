<?php

namespace Gini\CloudFS;

abstract class Cloud
{
    use \Gini\CloudFSTrait;

    /**
        * @brief 上传单个资源
        *
        * @param $uri
        * @param $filename
        *
        * @return 
     */
    abstract public function upload($file);

    /**
        * @brief 上传单个资源
        *
        * @param $uri
        * @param $filename
        *
        * @return 
     */
    abstract public function getImageURL($filename);

    abstract public function getThumbURL($filename, $width=0, $height=0);

    abstract public function getUploadConfig($type='cloud');

    private $_keys;
    private $_bucket;

    /**
        * @brief 设置云操作的key信息：accessKey，secretKey和uploadToken等
        *
        * @param $keys [mixed]
     */
    final public function setKeys($keys)
    {
        $this->_keys = $keys;
    }

    final public function getKeys()
    {
        return $this->_keys;
    }

    /**
        * @brief 设置存储段信息
        *
        * @param $bucket
        *
        * @return 
     */
    final public function setBucket($bucket)
    {
        $this->_bucket = $bucket;
    }

    final public function getBucket()
    {
        return $this->_bucket;
    }

}
