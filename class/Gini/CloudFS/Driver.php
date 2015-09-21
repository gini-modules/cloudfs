<?php

/**
 * @file Cloud.php
 * @brief 抽象类，定义了各个cloud至少应该实现的方法
 *
 * @author PiHiZi
 *
 * @version 0.1.0
 * @date 2014-07-11
 */

namespace Gini\CloudFS;

interface Driver
{
    public function upload(array $data);
    public function callback(array $data);
    public function config(array $data);
    public function delete($file);
    public function safeUrl($url);
}
