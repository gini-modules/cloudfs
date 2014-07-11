<?php
/**
* @file Cloud.php
* @brief 抽象类，定义了各个cloud至少应该实现的方法
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

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
