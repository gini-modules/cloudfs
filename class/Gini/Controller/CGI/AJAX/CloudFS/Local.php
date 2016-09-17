<?php

namespace Gini\Controller\CGI\AJAX\CloudFS;

class Local extends \Gini\Controller\CGI
{
    public function actionUpload($server = null)
    {
        $files = $this->form('files');
        if (empty($files)) {
            return \Gini\IoC::construct('\Gini\CGI\Response\Nothing');
        }

        $file = current($files);
        if (empty($file)) {
            return \Gini\IoC::construct('\Gini\CGI\Response\Nothing');
        }

        /* TODO: token机制 避免重复提交和跨域请求提交
        $form = $this->form('post');
        if (!$form['token']) return $this->showNothing();
        **/
        $cfs = \Gini\IoC::construct('\Gini\CloudFS\Server', $server);
        $result = (array) $cfs->upload($file);
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $result);
    }
}
