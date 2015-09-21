<?php

/**
 * @file Qiniu.php
 * @brief 七牛的ajax调用
 *
 * @author PiHiZi
 *
 * @version 0.1.0
 * @date 2014-07-11
 */
namespace Gini\Controller\CGI\AJAX\CloudFS;

class Qiniu extends \Gini\Controller\CGI
{

    public function actionCallback()
    {
        $form = $this->form();
        $fs = \Gini\IoC::construct('\Gini\CloudFS\Server', $form['server']);
        if (!$fs->driver->isFromQiniuServer()) {
            return false;
        }

        $result = $fs->driver->runServerCallback([
            'hash' => $form['hash'],
            'key' => $form['key'],
        ]);

        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $result);
    }

}
