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

    abstract public function upload($file);

    abstract public function getImageURL($filename);

    abstract public function getThumbURL($filename, $width=0, $height=0);

    abstract public function getUploadConfig();

}
