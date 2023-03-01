<?php
namespace App\Http\Controllers;

use Library\Custom\Publish\Prepare\Page;
use Library\Custom\Publish\Render\Content;
use Library\Custom\DirectoryIterator;
use Library\Custom\Model\Lists\Original;

class SourceController extends Controller {

    public $hp;
    public $page;

    public function init($request, $next) {

        $this->hp   = getUser()->getCurrentHp();
        $this->page = new Page($this->hp->id, $request);
        $this->view->topicPath('サイトの公開/更新');

        return $next($request);
    }
    
    public function src() {
        $ds = DIRECTORY_SEPARATOR;

        $render = new Content($this->hp->id, config('constants.publish_type.TYPE_PREVIEW'), $this->page);

        // imgs called in css
        if ($this->_request->has('top')) {
            $profile = $this->getUser()->getProfile();
        
            if (!$profile || !$profile->checkTopOriginal()) {
                $this->_forward404();
            }
            
            $data = array(
                'css'       => Original::ORIGINAL_IMPORT_TOPCSS,
                'js'        => Original::ORIGINAL_IMPORT_TOPJS,
                'images'    => Original::ORIGINAL_IMPORT_TOPIMAGE,
            );
            
            $path = $this->_request->top;
            if (!array_key_exists($path, $data)) {
                $this->_forward404();
            }
            
            if ($this->_request->has('images') && 'css' == $path) {
                $path = 'images'; // reset path for images inside css file
            }
            
            $di = new DirectoryIterator();
            $di->load(Original::getOriginalImportPath($profile->id, $data[$path]));
            
            $urls = parse_url(urldecode(urldecode($_SERVER['REQUEST_URI'])))['path'];
            $parts = explode('/', $urls);        
            $fileName = array_pop($parts);
            
            while ('' == $fileName) $fileName = array_pop($parts);
            
            $filePath = $di->getPath($fileName);
            if (!file_exists($filePath)) {
                $this->_forward404();
            }
            $info = pathinfo($filePath);
        }
        else if ($this->_request->has('imgs')) {

            $color  = explode('/imgs/', parse_url($_SERVER['REQUEST_URI'])['path']);
            $common = explode($ds, $color[1]);

            /**
             * ファイル検索
             *
             * @param       $baseDir
             * @param       $themeName
             * @param       $device
             * @param array $colorArray
             * @param array $commonArray
             * @return string
             */
            $searchFile = function ($baseDir, $themeName, $device, array $colorArray, array $commonArray) {

                // {$theme}/{$device}/{$color}/*
                $filePath = $baseDir.'/'.$themeName.'/'.$device.'/'.$colorArray[1];
                if (file_exists($filePath)) {
                    return $filePath;
                }

                // {$theme}/{$device}/*
                $filePath = $baseDir.'/'.$themeName.'/'.$device.'/'.$commonArray[1];
                if (file_exists($filePath)) {
                    return $filePath;
                }

                return '';
            };

            $filePath = $searchFile ($render->getBaseImgsPath(), $render->getThemeRow()->name, $this->getParam('device'), $color, $common);
            $info     = pathinfo($filePath);
        }
        // css, js, gif, png, jpeg ...
        else {
            $path = urldecode($this->_request->path);
            if(!$path){
                $urls = parse_url(urldecode(urldecode($_SERVER['REQUEST_URI'])))['path'];
                $parts = explode('/', $urls);        
                $path = array_pop($parts);
            }
            $info = pathinfo($path);

            // img_nowprinting_s.pngなど画像を呼びだす事もある
            $method_name = 'getBase'.ucfirst(strtolower($info['extension'])).'Path';
            if (!method_exists($render, 'getBase'.ucfirst(strtolower($info['extension'])).'Path')) {
                $method_name = 'getBaseImgsPath';
            }
            
            $baseDir = $render->$method_name();

            /**
             * ファイルを検索
             *
             * @param $baseDir
             * @param $themeName
             * @param $device
             * @param $colorName
             * @param $path
             * @return string
             */
            $searchFile = function ($baseDir, $themeName, $device, $colorName, $path) {

                // {$theme}/{$device}/{$color}/*
                if($colorName) {
                    $filePath = $baseDir.'/'.$themeName.'/'.$device.'/'.$colorName.'/'.basename($path);
                    if (file_exists($filePath)) {
                        return $filePath;
                    }                    
                }

                // {$theme}/{$device}/*
                $filePath = $baseDir.'/'.$themeName.'/'.$device.'/'.basename($path);
                if (file_exists($filePath)) {
                    return $filePath;
                }

                // standard/{$device}/*
                $themeName = 'standard';
                if (strpos($path, 'css') !== false) {
                    $path = str_replace("css/", "", $path);
                    $filePath  = $baseDir.'/'.$themeName.'/'.$device.'/'.$path;
                } else {
                    $filePath  = $baseDir.'/'.$themeName.'/'.$device.'/'.basename($path);
                }
                if (file_exists($filePath)) {
                    return $filePath;
                }

                // common/{$device}/*
                $themeName = 'common';
                $filePath  = $baseDir.'/'.$themeName.'/'.$device.'/'.basename($path);
                if (file_exists($filePath)) {
                    return $filePath;
                }
                
                // top/{$device}/*
                $profile = getUser()->getProfile();
                if ($profile && $profile->checkTopOriginal()) {
                    $data = array(
                        'css'       => Original::ORIGINAL_IMPORT_TOPCSS,
                        'js'        => Original::ORIGINAL_IMPORT_TOPJS,
                        'images'    => Original::ORIGINAL_IMPORT_TOPIMAGE,
                    );
                    
                    $parts = explode('/', $path);
                    foreach ($data as $import_key => $import_path) {
                        if (in_array($import_key, $parts) && in_array('top', $parts)) {

                            $di = new DirectoryIterator();
                            $di->load(Original::getOriginalImportPath($profile->id, $import_path));
                            
                            $fileName = array_pop($parts);
                    
                            while ('' == $fileName) $fileName = array_pop($parts);
                            
                            return $di->getPath($fileName);
                        }
                    }
                }              
                
                return '';
            };

            $filePath = $searchFile($baseDir, $render->getThemeRow()->name, $this->_request->device, $render->getColorRow()->name, $path);

        }
        if($filePath == "") {
            echo "";
            exit;
        }
        $render->setHeader($info['extension']);
        if ($info['extension'] == 'css') {
            header('Content-Type: text/css');
        }
        if(strpos($render->getThemeRow()->name, 'custom_color') !== false && $info['filename'] === 'color-setting') {
            $contents = file_get_contents($filePath);
            $rgbs = $render->conversionColorcodeToRbg($render->getColorCode());
            $rgb = $rgbs["red"] .",". $rgbs["green"] .",". $rgbs["blue"];
            $contents = str_replace('2,83,146', $rgb, $contents);
            echo $contents;
            exit();
        }
        echo file_get_contents($filePath);
        exit();
    }
}