<?php
namespace Library\Custom\View\Helper;

class Src extends  HelperAbstract {

    public function src($url, $previewUrl = null) {

        if (getActionName() == 'previewPage') {

            if ($previewUrl) {
                echo $previewUrl;
                return;
            }
            $request = app('request');
            echo urlSimple('src', 'source', 'default', [
                'id' => $request->id,
                'parent_id' => $request->parent_id,
                'device' => $request->device,
                'path' => urlencode($url)
                ]);
            return;
        }

        // device
        $device = '';
        $path   = $this->_view->getPath();
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $device) {

            if ($device == 'pc' || $device == 'sp') {
                break;
            }
        }

        echo DIRECTORY_SEPARATOR.$device.DIRECTORY_SEPARATOR.$url;
    }

}