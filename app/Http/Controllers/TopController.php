<?php
namespace App\Http\Controllers;

use Library\Custom\DirectoryIterator;
use Library\Custom\Model\Lists;

class TopController extends Controller {

    public function init($request, $next) {
        
        $profile = getUser()->getProfile();
        
        if (!$profile || !$profile->checkTopOriginal()) {
            $this->_forward404();
        }
        
        $this->cid = $profile->id;
        
        $this->di = new DirectoryIterator();
        return $next($request);
    }
    
    public function js()
    {
        $this->di->load(Lists\Original::getOriginalImportPath($this->cid, config('constants.original.ORIGINAL_IMPORT_TOPJS')));
        
        $fileName = $this->getFileName();
        $filePath = $this->di->getPath($fileName);
        
        if (file_exists($filePath)) {
            $info = pathinfo($filePath);
            header('Accept-Ranges: bytes');
            
            $this->_output(file_get_contents($filePath), $this->_contentType($info['extension']));
        }
        
        $this->_forward404();
    }
    
    public function images()
    {
        $this->di->load(Lists\Original::getOriginalImportPath($this->cid, config('constants.original.ORIGINAL_IMPORT_TOPIMAGE')));
        
        $fileName = $this->getFileName();
        $filePath = $this->di->getPath($fileName);
        // var_dump($filePath);die;
        if (file_exists($filePath)) {
            $info = pathinfo($filePath);
            header('Accept-Ranges: bytes');
            
            $this->_output(file_get_contents($filePath), $this->_contentType($info['extension']));
        }
        
        $this->_forward404();
    }
    
    public function css()
    {
        $this->di->load(Lists\Original::getOriginalImportPath($this->cid, config('constants.original.ORIGINAL_IMPORT_TOPCSS')));
        
        $fileName = $this->getFileName();
        $filePath = $this->di->getPath($fileName);
        
        if (file_exists($filePath)) {
            $info = pathinfo($filePath);
            header('Accept-Ranges: bytes');
            
            $this->_output(file_get_contents($filePath), $this->_contentType($info['extension']));
        }
        
        $this->_forward404();
    }
    
    protected function getFileName()
    {
        $urls = parse_url(urldecode(urldecode($_SERVER['REQUEST_URI'])))['path'];
        $parts = explode('/', $urls);
        
        return end($parts);
    }
    
	protected function _contentType($ext)
    {
        switch ($ext) {
            case 'js':
                return "text/javascript";
                break;
            case 'css':
                return "text/".$ext;
                break;
            case 'jpg':
                return "image/jpeg";
                break;
            case 'png':
            case 'gif':
                return "image/".$ext;
				break;
        }

        throw new \InvalidArgumentException('unknown type');
    }

    protected function _output($content, $type)
    {
        header('Content-Type: ' . $type);
		header('Content-Length: ' . strlen($content));
		echo $content;
		exit();
    }
}