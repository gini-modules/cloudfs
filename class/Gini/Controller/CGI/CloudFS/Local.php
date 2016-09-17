<?php

namespace Gini\Controller\CGI\CloudFS;

class Local extends \Gini\Controller\CGI
{
    public function __index() {
        $form = $this->form();
        $filename = ltrim(trim($form['f']), '/');
        if (strncmp($file, './', 2)==0 || strncmp($filename, '../', 3)==0) {
            return;
        }

        $config = \Gini\Config::get('cloudfs.server')[$form['s']];
        $options = $config['options'];
        $root = $options['root'] ?: APP_PATH.'/'.DATA_DIR.'/cloudfs';

        $file = $root.'/'.$filename;

    	($finf = finfo_open(FILEINFO_MIME)) or function () use ($file) {
    		throw new \BadFunctionCallException("File '$file' not found", 404);
    	};

        $mime = finfo_file($finf, $file);
        finfo_close($finf);
        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
        header('ETag: ' . md5(dirname($file)));
        if ($expire) {
            header('Cache-Control: maxage=' . strtotime($expire));
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + strtotime($expire)) . ' GMT');
        }
        header('Content-Disposition: inline; filename=' . urlencode(basename($file)));
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Connection: close');
        readfile($file);

    }
}
