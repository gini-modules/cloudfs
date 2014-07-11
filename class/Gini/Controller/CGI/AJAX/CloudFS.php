<?php

namespace Gini\Controller\CGI\AJAX;

class CloudFS extends \Gini\Controller\CGI
{
    final public function showJSON($data)
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $data);
    }

    public function actionGetConfig()
    {
        $form = $this->form();
        $cloud = $form['cloud'];
        $type = $form['type'];
        $cfs = \Gini\IoC::construct('\Gini\CloudFS', $cloud);
        $config = $cfs->getUploadConfig($type);
        return $this->showJSON($config);
    }
}
