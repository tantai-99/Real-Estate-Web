<?php
namespace Library\Custom;

class View
{
    protected $_classs = [];
    protected $_filters = [];
    protected $_paths = [];
    protected $_path = null;

    public function __call($methodName, array $args)
    {
        $className = ucfirst($methodName);
        if (isset($this->_classs[$className])) {
            $this->_classs[$className]->setViewParams($this);
            return call_user_func_array(array($this->_classs[$className], $methodName), $args);
        }

        throw new \RunTimeException('There is no method with the given name to call');
    }

    public function addHelperPath($paths, $prefix) {
        foreach($paths as $path) {
            foreach(scandir(base_path().'/'.$path) as $filename) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                $className = str_replace('.php', '', $filename);
                $class = $prefix.$className;
                $this->_classs[$className] = new $class();
            }
            
        }
    }
    public function addFilterPath($paths, $prefix) {
        foreach($paths as $path) {
            foreach(scandir(base_path().'/'.$path) as $filename) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                $className = str_replace('.php', '', $filename);
                $class = $prefix.$className;
                $this->_filters[$className] = new $class();
            }
            
        }
    }

    public function __set($columnName, $value) {
        return $this->{$columnName} = $value;
    }

    public function __get($columnName) {
        if(isset($this->{$columnName})) {
            return $this->{$columnName};
        }

        return null;
    }

    public function setViewPath(array $paths, $file = null) {
        if (!$file) {
            view()->replaceNamespace('publish', $paths[0]);
        }
        $exits = false;
        $this->setPaths($paths);
        foreach($this->getPaths() as $path) {
            if (file_exists($path.'/'.$file)) {
                view()->replaceNamespace('publish', $path);
                $this->_path = $path;
                $exits = true;
                break;
            }
        }
        if (!$exits) {
            throw new \Exception('file not exit:'.$file);
        }
    }

    public function getPaths() {
        return $this->_paths;
    }

    public function setPaths($paths) {
        $this->_paths = $paths;
    }

    public function getPath() {
        return $this->_path;
    }

    public function setParams($params) {
        foreach($params as $name=>$value) {
            $this->{$name} = $value;
        }
    }

    public function partial($file, $params = []) {
        $ext = '.blade.php';
        if (!strpos($file, $ext)) {
            $file .= $ext;
        }
        $this->setViewPath($this->getPaths(), $file);
        $this->setParams($params);
        return $this->render($file);
    }

    public function getScriptPath($file) {
        $exits = false;
        foreach($this->getPaths() as $path) {
            if (file_exists($path.'/'.$file)) {
                $exits = true;
                return $path.'/'.$file;
            }
        }
        if (!$exits) {
            throw new \Exception('file not exit:'.$file);
        }

    }

    public function render($filename) {
        view()->flushFinderCache();
        $filenames = explode('.', $filename);
        $content = '';
        $extention = 'blade';
        if (count($filenames) > 1) {
            $extention = $filenames[1];
        }
        switch ($extention) {
            case 'html':
                $content = file_get_contents($this->_path.'/'.$filename);
                break;
            case 'blade':
                $filename =  str_replace(['/', '.blade.php'], ['.', ''], $filenames[0]);
                $content = view('publish::'.$filename, ['view'=> $this])->render();
                break;
        }
        return $this->filterContent($content);
    }

    public function filterContent($content) {
        foreach($this->_filters as $filter) {
            $filter->setView($this);
            $content = $filter->filter($content);
        }
        return $content;
    }

}