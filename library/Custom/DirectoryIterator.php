<?php
namespace Library\Custom;
use stdClass;
use Library\Custom\Model\Lists\Original;
use Exception;
class DirectoryIterator
{
  /**
   * store all file and folder
   */
  protected $_storage;
  
  /**
   * Current direction
   */
  public $destination;
  
  /**
   * accepted extensions
   */
  public $fileExtensions = null;
  
  /**
   * accepted file name
   */
  public $specialFiles = null;
  
  /**
   * load root folder
   */
  public $ignoreRootDir = true;

  /**
   * backup folder name
   */
  public $backupDir = '__backup';
  
  public $tmpDir = '__tmp';
  
  public $error = '';

  protected $prefix = '.html';

  public function __construct($makeBackup=false)
  {
    $this->_storage = array();
    
    $this->makeBackup = $makeBackup;
  }
  
  public function load($destination) {
    $this->_storage = array();

    $this->destination = $destination;
    
    $this->root =  $destination . '/' . '..';
    
    foreach (new \RecursiveDirectoryIterator($destination) as $fileInfo) {
      if($this->tmpDir == $fileInfo->getFilename() ||  $this->backupDir == $fileInfo->getFilename()){
          continue;
      }
      //$fileInfo->isDot() && 
      if('.' == $fileInfo->getFilename()){
          continue;
      }
      
      $fp = new stdClass();
      $fp->name           = $fileInfo->getFilename();
      $fp->type           = $fileInfo->getType();
      $fp->title          = '';
      $fp->filePath       = $fileInfo->getPathname();
      $fp->rootPath       = dirname($destination);
      $fp->isFile         = $fileInfo->isFile() && !$this->isFolderOriginal($fp->name);
      $fp->isDir          = $fileInfo->isDir() || $this->isFolderOriginal($fp->name);
      $fp->isLink         = $fileInfo->isLink();
      $fp->data           = array(
                                  'can_edit_name'=> false,
                                  'can_edit_data'=> false
                            );
      $fp->hasBackup      = is_file($this->getBackupFilePath($fp->filePath));
      if ($fp->isDir) {
        $fp->updatedAt     = $this->getDateTimeUpdateFolder($destination.'/'.$fp->name);
      } else {
        $dt = new \Datetime();
        $dt->setTimeZone(new \DatetimeZone('Asia/Tokyo'));
        $dt->setTimestamp(filemtime($fp->filePath));
        $fp->updatedAt     = $dt->format('Y年n月j日 H : i');
      }
      $fp->timestamp      = $fileInfo->getMTime();
      
      $fp->hidx           = $this->makeHashKey($fp->filePath);
      $this->_storage[$fp->hidx] = $fp;
    }
  }

  public function getDateTimeUpdateFolder($destination) {
    $updatedAts = [];
    foreach (new \RecursiveDirectoryIterator($destination) as $fileInfo) {
      $dt = new \Datetime();
      $dt->setTimeZone(new \DatetimeZone('Asia/Tokyo'));
      $dt->setTimestamp(filemtime($fileInfo->getPathname()));
      $updatedAts[]    = $dt->format('Y年n月j日 H : i');
    }
    if (empty($updatedAts)) {
      $dt = new \Datetime('now', new \DatetimeZone('Asia/Tokyo'));
      return $dt->format('Y年n月j日 H : i');
    }
    return max($updatedAts);
  }

  public function get($name) {
    $filePath = $this->getPath($name);
    $hidx = $this->makeHashKey($filePath);
    
    return isset($this->_storage[$hidx]) ? $this->_storage[$hidx] : null;
  }
  
  public function getPath($name) {
      return $this->destination . '/' . $name;
  }

  public function getRootDestination() {
    return $this->root;
  }
  
  public function getDestination()
  {
    return $this->destination;
  }
  
  public function getTmpDestination()
  {
    $path = $this->getPath($this->tmpDir);
    return $path;
  }
  
  public function setDestination($destination)
  {
    $this->destination = $destination;
    
    return $this;
  }
  
  public function removeDir($path) {
    \Storage::disk('s3')->deleteDirectory($this->pathS3($path));
  }

  public function pathS3($path) {
    return str_replace('s3://'.env('AWS_BUCKET'), '', $path);
  }
  
  public function removeTmpDir() {
    $this->removeDir($this->getTmpDestination());
    return true;
  }
  
  public function mergeDir() {
        $path = $this->getTmpDestination();
        if (file_exists($path)) {
            foreach (new \RecursiveDirectoryIterator($path) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    if ($this->checkIsSpecialFile($fileInfo->getFilename())) {
                        $this->backupFile($fileInfo->getFilename());
                    }
                    
                    $to = $this->getDestination() . '/' . $fileInfo->getFilename();
                    $this->moveFile($fileInfo->getPathname(), $to, true);
                }
            }
            @rmdir($path);
        }
  }
  
  public function moveFile($from, $to, $override=true) {
    if (file_exists($to)) {
        
        if (false === $override) return;
        
        if (is_file($to)) {
            unlink($to);
        }
    }

    rename($from, $to);
  }

  public function makeFile($file_name, $data, $override=true) {
    $filePath = $this->getPath($file_name);
    
    $content = $data;
    if (false === $override) {
        $content = $this->readFileContent($file_name);
        $content .= $data;
    }
    
    file_put_contents($filePath, $content);
  }
  
  public function copyFile($from, $to, $override=true) {
    if (file_exists($to)) {
        
        if (false === $override) return;
        
        if (is_file($to)) {
            unlink($to);
        }
    }

    copy($from, $to);
  }
  
  public function getBackupFilePath($path) {
    $parts = explode('/' , $path);
    $tmp_name = array_pop($parts);
    array_push($parts, $this->backupDir, $tmp_name);
    $path = implode('/', $parts);
    
    return $path;
  }
  
  public function backupFile($file_name, $override=false){
    $path = $this->getPath($file_name);
    $to = $this->getBackupFilePath($path);
    if (!is_file($path) && (basename(dirname($path)) !== config('constants.original.ORIGINAL_IMPORT_TOPKOMA'))) {
      $path = $this->getTmpDestination().'/'.$file_name;
    }
    if (is_file($path)) {
      $this->copyFile($path, $to, $override);
    }
  }
  
  protected function makeHashKey($name) {
    return md5($name);
  }
  
  public function ignoreRootDir($ck = true) {
    $this->ignoreRootDir = $ck;
  }
  
  public function setRootUrl($url) {
    $this->setUrl('..', $url);
  }
  
  public function setUrl($dirName, $url) {
    if ($this->checkIsExistFile($dirName, 'b')) {
      $fp = $this->get($dirName);
      $fp->data['link'] = $url;
    }
  }
  
  public function setDataFile($dirName, $data) {
    if ($this->checkIsExistFile($dirName, 'a')) {
      $fp = $this->get($dirName);

      if ($fp->filePath == $this->root && isset($data['link'])) {
        unset($data['link']);
      }

      if ($this->checkIsSpecialFile($fp->name)) {
        unset($data['can_edit_name']);
        unset($data['can_edit_data']);
      }

      $fp->data = array_merge($fp->data, $data);
    }
  }

  public function setData($data) {
    foreach ($this->_storage as $key => $fp) {
      if ($fp->filePath == $this->root && isset($data['link'])) unset($data['link']);

      $this->setDataFile($fp->name, $data);
    }
  }
  
  public function checkIsValidFile($fileName) {
    if (!$this->checkIsValidName($fileName) || !$this->checkIsAllowExtension($fileName)) {
        return false;
    }
    
    if (!$this->specialFiles || false === $this->isOnlyUploadSpecialFiles) {
      return true;
    }
    
    return $this->checkIsSpecialFile($fileName);
  }
  
  function checkIsValidName($fileName) {
      #3809, no validate
      return true;
    //return preg_match('/^([-\.\w]+)$/', $fileName) > 0;
  }
  
  public function checkIsAllowExtension($fileName)
  {
    $exts = $this->fileExtensions ?: array();
    
    if (0 == count($exts)) return true;
    
    $ext_parts = explode('.', $fileName);
    
    return in_array(end($ext_parts), $exts);
  }
  
  public function checkIsSpecialFile($fileName)
  {
    $files = $this->specialFiles ?: array();
    
    foreach ($files as $fname) {
      if ($fname === $fileName) return true;
      
      // is special multi file. (*.txt *.html *.css etc...)
      $isPattern = count(explode('*', $fname)) > 1;
      if ($isPattern) {
        $reg = '/^' . str_replace('*', '\\w+', $fname) . '$/';
        
        if (preg_match($reg, $fileName, $matches)) {
            return true;
        }
      }
    }
    
    return false;
  }
  
  public function checkIsExistFile($fileName, $a = '')
  {
    
    $filePath = $this->getPath($fileName);
    $hidx = $this->makeHashKey($filePath);
    
    return isset($this->_storage[$hidx]);
  }
  
  public function setSpecialFiles($files, $uploaded=true) {
    if (!is_array($files)) {
      throw new Exception ('List special files must be an array.');
      exit();
    }
    
    if (count($files) > 0) {
      $this->specialFiles = $files;
      $this->isOnlyUploadSpecialFiles = $uploaded;
    }
  }
  
  public function setExtensions($exts) {
    if (!is_array($exts)) {
      throw new Exception ('List extensions must be an array.');
      exit();
    }
    
    $this->fileExtensions = $exts;
  }
  
  public function getExtensions() {
    return $this->fileExtensions;
  }
  
  public function revertFileContent($fileName) {
    if (!$this->checkIsExistFile($fileName)) {
      return false;
    }
    
    try {
      $filePath = $this->getPath($fileName);
      $bkFile = $this->getBackupFilePath($filePath);
      $this->moveFile($bkFile, $filePath, true);
      
      return true;
    } catch (Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }
  
  public function removeFile($fileName) {
    if (!$this->checkIsExistFile($fileName)) {
      return false;
    }
    
    try {
      unlink($this->getPath($fileName));
      return true;
    } catch (Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }
  
  public function updateFileName($old, $new) {
    if (false == $this->checkIsValidName($new)) {
      return false; 
    }
    
    if (false == $this->checkIsAllowExtension($new)) {
      return false;
    }
    
    if ($this->checkIsExistFile($new)) {
      return false;
    }
    
    rename($this->getPath($old), $this->getPath($new));
    
    return true;
  }
  
  public function updateFileContent($fileName, $data) {
    if (!$this->checkIsExistFile($fileName)) {
      return false;
    }
    
    try {
      file_put_contents($this->getPath($fileName), $data, LOCK_EX);
      return true;      
    } catch (Exception $e) {
      $this->error = $e->getMessage();
      return false;
    }
  }
  
  public function readFileContent($fileName) {
    if (!$this->checkIsExistFile($fileName)) {
      throw new Exception ('File ' . $fileName . ' not found.');
      exit();
    }
    
    try {
      $response = file_get_contents($this->getPath($fileName), FILE_USE_INCLUDE_PATH);
      return $response;
    } catch (Exception $e) {
        throw new Exception ($e->getMessage());
        exit();
    }
  }
  
  public function getList($desc = false)
  {
    $folders = array();
    $files = array();
    
    $root = array();
    usort($this->_storage, $this->orderByFnCallback('name'));
    if ($desc) $this->_storage = array_reverse($this->_storage);
    foreach ($this->_storage as $fp)
    {
      if ($fp->isFile) {
        $files[] = $fp;
      } else if ($fp->isDir) {
        if ('..' == $fp->name) {
          if (!$this->ignoreRootDir) $root[] = $fp;
          continue; 
        }
        
        $folders[] = $fp;
      }
    }
    
    if ($desc) return array_merge($root, $files, $folders);
    return array_merge($root, $folders, $files);
  }
  
  public function getListByDate($desc = false)
  {
    $folders = array();
    $files = array();
    
    $root = array();
    usort($this->_storage, $this->orderByFnCallback('timestamp'));
    if ($desc) $this->_storage = array_reverse($this->_storage);

    foreach ($this->_storage as $fp)
    {
      if ($fp->isFile) {
        $files[] = $fp;
      } else if ($fp->isDir) {
        if ('..' == $fp->name) {
          if (!$this->ignoreRootDir) $root[] = $fp;
          continue; 
        }
        
        $folders[] = $fp;
      }
    }
    
    if ($desc) return array_merge($root, $files, $folders);
    return array_merge($root, $folders, $files);
  }
  
  private function orderByFnCallback($key) {
    return function ($a, $b) use ($key) {
        return strnatcmp($a->{$key}, $b->{$key});
    };
  }
  
  public function initialImportHtmlDir($company_id) {
      if ('' === $company_id) {
          throw new Exception('No company id.');
          return;
      }
      $original = new Original();
      $dir = $original->getOriginalImportPath($company_id);
      $this->fakeFolderOriginal($dir, config('constants.original.ORIGINAL_IMPORT_TOPKOMA'));
      $this->fakeFolderOriginal($dir, config('constants.original.ORIGINAL_IMPORT_TOPCSS'));
      $this->fakeFolderOriginal($dir, config('constants.original.ORIGINAL_IMPORT_TOPJS'));
      $this->fakeFolderOriginal($dir, config('constants.original.ORIGINAL_IMPORT_TOPIMAGE'));
  }

  public function fakeFolderOriginal($path, $name) {
    $path = $path . '/' . $name;
    if (!file_exists($path)) {
      $fp = new stdClass();
      $fp->name           = $name;
      $fp->type           = 'dir';
      $fp->title          = '';
      $fp->filePath       = $path;
      $fp->rootPath       = dirname($this->destination);
      $fp->isFile         = false;
      $fp->isDir          = true;
      $fp->isLink         = true;
      $fp->data           = array(
                              'can_edit_name'=> false,
                              'can_edit_data'=> false
                            );

      $fp->hidx           = $this->makeHashKey($fp->filePath);
      $fp->hasBackup      = isset($this->_storage[$fp->hidx]);
      $dt = new \Datetime('now', new \DatetimeZone('Asia/Tokyo'));
      $fp->updatedAt     = $dt->format('Y年n月j日 H : i');
      $fp->timestamp      = '-';//$fp->hasBackup ? $data->create_special_date : '-';
      $this->_storage[$fp->hidx] = $fp;
    }

  }
  
    private function fakeSpecial($data, $type) {
        $fp = new stdClass();
        $fp->name           = "special{$data->id}_{$type}.html";
        $fp->type           = 'html';
        $fp->title          = $data->title;
        $fp->filePath       = $this->destination . '/' . $fp->name;
        $fp->rootPath       = dirname($this->destination);
        $fp->isFile         = true;
        $fp->isDir          = false;
        $fp->isLink         = false;
        $fp->data           = array();

        $fp->hidx           = $this->makeHashKey($fp->filePath);
        $fp->hasBackup      = isset($this->_storage[$fp->hidx]);
        $fp->updatedAt     = $fp->hasBackup ? $data->create_special_date : '-';
        $fp->timestamp      = $fp->hasBackup ? $data->create_special_date : '-';
        
        return $fp;
    }
  
    public function mergeSpecialFiles($specials) {
        foreach ($specials as $special) {
            $fakePC = $this->fakeSpecial($special, 'pc');
            $fakeSP = $this->fakeSpecial($special, 'sp');
            
            if ($fakePC->hasBackup) unset($this->_storage[$fakePC->hidx]);
            if ($fakeSP->hasBackup) unset($this->_storage[$fakeSP->hidx]);
            
            // if (!$fakePC->hasBackup || !$fakeSP->hasBackup) continue;
            $this->_storage[$fakePC->hidx] = $fakePC;
            $this->_storage[$fakeSP->hidx] = $fakeSP;
        }
    }

    public function getMessageError()
    {
        return $this->error;
    }
    
    public function getFile($name){
      return $this->get($name.$this->prefix);
    }

    public function isFolderOriginal($filename) {
      return in_array($filename, array(
        config('constants.original.ORIGINAL_IMPORT_TOPROOT'),
        config('constants.original.ORIGINAL_IMPORT_TOPCSS'),
        config('constants.original.ORIGINAL_IMPORT_TOPJS'),
        config('constants.original.ORIGINAL_IMPORT_TOPIMAGE'),
        config('constants.original.ORIGINAL_IMPORT_TOPKOMA')
      ));
    }
}