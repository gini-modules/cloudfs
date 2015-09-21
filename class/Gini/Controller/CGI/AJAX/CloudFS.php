<?php

/**
 * @file CloudFS.php
 * @brief ajax通用调用，所有cloud中需要同意实现的异步请求处理放在这里
 *
 * @author PiHiZi
 *
 * @version 0.1.0
 * @date 2014-07-11
 */
namespace Gini\Controller\CGI\AJAX;

class CloudFS extends \Gini\Controller\CGI
{
    public function actionConfig()
    {
        $form = $this->form();
        $fs = \Gini\IoC::construct('\Gini\CloudFS\Server', $form['server']);
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $fs->config($form['file']));
    }

    public function actionUploaded()
    {
        $form = $this->form();
        $fs = \Gini\IoC::construct('\Gini\CloudFS\Server', $form['server']);
        $data = (array) $fs->callback((array) $form['data']);
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $data);
    }

}
