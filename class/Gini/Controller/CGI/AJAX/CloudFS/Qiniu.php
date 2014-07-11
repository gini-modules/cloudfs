<?php

namespace Gini\Controller\CGI\AJAX\CloudFS;

class Qiniu extends \Gini\Controller\CGI
{
    private function showNothing()
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\Nothing');
    }
    private function showJSON($data)
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $data);
    }

    public function actionCallback()
    {
        $cfs = \Gini\IoC::construct('\Gini\CloudFS', \Gini\CloudFS\Qiniu::CLOUD_NAME);
        $bool = $cfs->isFromQiniuServer();
        if (!$bool) return $this->showNothing();
    }

    public function actionUpload()
    {
        $files = $this->form('files');
        if (empty($files)) return $this->showNothing();
        $file = current($files);
        if (empty($file)) return $this->showNothing();
        /** TODO: token机制 避免重复提交和跨域请求提交
        $form = $this->form('post');
        if (!$form['token']) return $this->showNothing();
        **/
        $cfs = \Gini\IoC::construct('\Gini\CloudFS', \Gini\CloudFS\Qiniu::CLOUD_NAME);
        $result = $cfs->upload($file);
        return $result;
    }
}
