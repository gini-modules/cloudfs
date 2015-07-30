<?php

namespace Gini\Module;

class CloudFS
{
    public static function setup() 
    {
    }

    public static function diagnose()
    {
        $error = [];

        $confs = (array) \Gini\Config::get('cloudfs.client');
        foreach ($confs as $key=>$conf) {
            if (!is_array($conf) || !isset($conf['driver']) || strtolower($conf['driver'])!='localfs') {
                continue;
            }
            $callbacks = (array) $conf['callbacks'];
            foreach ($callbacks as $callback) {
                if (!is_callable($callback)) {
                    $error[] = "{$callbacks} 不存在";
                }
            }
            $cfs = \Gini\IoC::construct('\Gini\CloudFS\Client', $key);
            $root = $cfs->getLocale();
            if (!is_writable($root)) {
                $error[] = "请确保目录可写：{$root}";
            }
        }

        if (!empty($error)) return $error;
    }
}
