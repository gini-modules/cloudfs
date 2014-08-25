<?php
/**
* @file Qiniu.php
* @brief 七牛的ajax调用
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

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
        $form = $this->form();
        $client = $form['x:client'];
        $cfs = \Gini\IoC::construct('\Gini\CloudFS', $client);
        $bool = $cfs->isFromQiniuServer();
        if (!$bool) return $this->showNothing();
        $result = $cfs->runServerCallback($this->form());
        return $this->showJSON($result);
    }

    public function actionParseData()
    {
        $form = $this->form();
        $data = $form['data'];
        if (!$data['key']) return $this->showNothing();
        $cloud = $form['cloud'];
        $cloud = \Gini\IoC::construct('\Gini\CloudFS', $cloud);
        $image = $cloud->getImageURL($data['key']);
        return $this->showJSON($image);
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
        $form = $this->form('post');
        $client = $form['cfs:cloud'];
        $cfs = \Gini\IoC::construct('\Gini\CloudFS', $client);
        $result = $cfs->upload($file);
        return $this->showJSON($result);
    }
}
