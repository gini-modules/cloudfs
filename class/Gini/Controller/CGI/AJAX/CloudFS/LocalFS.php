<?php

namespace Gini\Controller\CGI\AJAX\CloudFS;

class LocalFS extends \Gini\Controller\CGI
{
    private function showNothing()
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\Nothing');
    }
    private function showJSON($data)
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $data);
    }

    public function actionUpload($client = null)
    {
        $files = $this->form('files');
        if (empty($files)) {
            return $this->showNothing();
        }
        $file = current($files);
        if (empty($file)) {
            return $this->showNothing();
        }

        /* TODO: token机制 避免重复提交和跨域请求提交
        $form = $this->form('post');
        if (!$form['token']) return $this->showNothing();
        **/

        $cfs = \Gini\IoC::construct('\Gini\CloudFS\Client', $client);
        $result = (array) $cfs->upload($file);

        return $this->showJSON($result);
    }
}
