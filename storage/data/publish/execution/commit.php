<?php

error_reporting(0);

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

//echo var_export((new Sync())->run());
echo json_encode((new Sync())->perform(),JSON_UNESCAPED_UNICODE);

class Sync {

    /**
     * @var Handler
     */
    public $handle;

    /**
     * @var Path
     */
    public $path;

    public $publishType;
    
    public $checkIP;

    protected   $log    ;

    /**
     * 公開時に作業領域の利用(_green <=> _blue)
     * true : 無条件利用(ただし初回を除く)
     * false: GMOサーバのみ
     */
    private $useWork = true;

    public static $defaultCurColor  = '_blue';
    public static $defaultWorkColor = '_green';

    public static $colorSetting;

    public function __construct() {
        $this->checkIP = new CheckIP();
        $this->handle = new Handler();
        $this->path   = new Path();

        // 公開領域・非公開領域のリンク先カラー設定
        self::$colorSetting = [
            'publicCurColor'  => self::$defaultCurColor,
            'filesCurColor'   => self::$defaultCurColor,
            'publicWorkColor' => self::$defaultWorkColor,
            'filesWorkColor'  => self::$defaultWorkColor
        ];
    }
    
    public function perform() {
    	$this->log = new DLog();
    	$this->log->log('commit.php start');
    	$result = [ "success" => false ];
    	try {
    		$run = $this->run();
    		$result = [ "success" => $run, "result" => $run];
    	} catch (Exception $e) {
    		$this->log->error('commit.php error', $e);
        	$exception = array(
        				'message' => $e->getMessage(),
        				'file' => $e->getFile(),
        				'line' => $e->getLine(),
        				'trace' => $e->getTraceAsString()
            );
        	$result = [ "success" => false, "error" => $exception ];
        	ob_start() ;
			var_dump( $result ) ;
			$out = ob_get_contents() ;
			ob_end_clean() ;
			$this->log->log( $out ) ;
    	}
    	$this->log->log('commit.php finish');
        return $result;
    }

    public function run() {

    	$this->log->log( "run() start" ) ;
    	// delete files except zip file
        foreach ($this->handle->scan($this->path->tempDir()) as $name) {

        	if (strtolower((new SplFileInfo(basename($name)))->getExtension()) !== 'zip') {
                $file = $this->path->tempDir().DS.$name	;
        		$this->handle->rmDir( $file ) ;
                $this->log->log( "delete {$file}" ) ;
        	};
        }

        // unzip
        if (!$this->unzip()) {
        	throw new Exception("unzipに失敗しました。");
//             return false;
        };

        // public dirname
        $this->path->publicDirname = $this->getPublicDirname();

        // publish type
        $this->publishType = $this->getPublishType();

        if($this->useWork == false) {
            // 公開先がpublic(本番&GMOのみ) Symlink対応
            switch($this->publishType) {
                case 'public':
                    if(!file_exists(dirname(__FILE__) . '/../isDemoSite')) {
                        $this->useWork = true;
                    }
                    break;
                default:
                    break;
            }
        }

        // 公開ディレクトリ、filesがともに存在することを確認する
        $public_cur_dir  = null;
        $files_cur_dir   = null;
        $public_work_dir = null;
        $files_work_dir  = null;

        if($this->useWork) {
            // 公開ディレクトリの有無確認: なければ直接書き出し処理
            if(!is_dir($this->path->publicDir())) {
                $this->useWork = false;
            }
            // 非公開ディレクトリの有無確認: なければ直接書き出し処理
            if(!is_dir($this->path->filesDir().DS.$this->publishType)) {
                $this->useWork = false;
            }
        }

        // 公開,非公開ディレクトリのシンボリックリンク状態を確認する
        if($this->useWork) {
            // 公開ディレクトリがシンボリックリンク化
            if(is_link($this->path->publicDir())) {
                if(preg_match('/' . self::$defaultWorkColor . '$/', readlink($this->path->publicDir()))) {
                    self::$colorSetting['publicCurColor']  = self::$defaultWorkColor;
                    self::$colorSetting['publicWorkColor'] = self::$defaultCurColor;
                }
                $public_cur_dir = $this->path->publicCurDir();
            } else {
                $public_cur_dir = $this->path->publicCurDir();

                if(rename($this->path->publicDir(), $public_cur_dir)) {
                    $symlink_cmd = [];
                    $symlink_cmd[] = "cd " . dirname($public_cur_dir);
                    $symlink_cmd[] = "ln -s " . basename($public_cur_dir) . " " . basename($this->path->publicDir());

                    exec(implode(" && ", $symlink_cmd), $output, $return_var);

                    if($return_var != 0) {
                        rename($public_cur_dir, $this->path->publicDir());
                        $this->useWork = false;
                    }
                } else {
                    $this->useWork = false;
                }
            }

            // 非公開ディレクトリがシンボリックリンク化
            if(is_link($this->path->filesDir().DS.$this->publishType)) {
                if(preg_match('/' . self::$defaultWorkColor . '$/', readlink($this->path->filesDir().DS.$this->publishType))) {
                    self::$colorSetting['filesCurColor']  = self::$defaultWorkColor;
                    self::$colorSetting['filesWorkColor'] = self::$defaultCurColor;
                }
                $files_cur_dir = $this->path->filesDir().DS.$this->publishType . self::$colorSetting['filesCurColor'];
            } else {
                $files_cur_dir = $this->path->filesDir().DS.$this->publishType . self::$colorSetting['filesCurColor'];

                if(rename($this->path->filesDir().DS.$this->publishType, $files_cur_dir)) {
                    $symlink_cmd = [];
                    $symlink_cmd[] = "cd " . dirname($files_cur_dir);
                    $symlink_cmd[] = "ln -s " . basename($files_cur_dir) . " " . $this->publishType;

                    exec(implode(" && ", $symlink_cmd), $output, $return_var);

                    if($return_var != 0) {
                        rename($files_cur_dir, $this->path->filesDir().DS.$this->publishType);
                        $this->useWork = false;
                    }
                } else {
                    $this->useWork = false;
                }
            }
        }

        if($this->useWork) {
            $this->log->log('working directory.');
            $this->log->log('public_work_dir :' . $this->path->publicWorkDir());
            $this->log->log('files_work_dir  :' . $this->path->filesDir().DS.$this->publishType . self::$colorSetting['filesWorkColor']);
        }

        if($this->useWork) {
            $this->log->log('rsync public start');
            // 公開用作業ディレクトリの特定および作成
            $public_work_dir = $this->path->publicWorkDir();
            $this->handle->mkdir($public_work_dir);

            // 公開領域の全ファイルを作業領域にコピー
            $rsync_cmd = sprintf("rsync -avu --delete --exclude 'commit.php' %s/ %s", $public_cur_dir, $public_work_dir);
			$this->log->log('comand : ' . $rsync_cmd);

            $output = [];
            exec($rsync_cmd, $output, $return_var);
            if($return_var != 0) {
                throw new Exception("公開領域から作業領域へのrsyncに失敗しました。");
            }
            // foreach($output as $ol) {
            //     $this->log->log('rsync public output: '. $ol);
            // }
            $this->log->log('rsync public end');

            $this->log->log('rsync files start');
            // 非公開用作業ディレクトリの特定および作成
            $files_work_dir = $this->path->filesDir().DS.$this->publishType . self::$colorSetting['filesWorkColor'];
            $this->handle->mkdir($files_work_dir);

            // 非公開領域の全ファイルを作業領域にコピー
            $rsync_cmd = sprintf("rsync -avu --delete %s/ %s", $files_cur_dir, $files_work_dir);
			$this->log->log('comand : ' . $rsync_cmd);

            $output = [];
            exec($rsync_cmd, $output, $return_var);
            if($return_var != 0) {
                throw new Exception("公開領域から作業領域へのrsyncに失敗しました。");
            }
            // foreach($output as $ol) {
            //     $this->log->log('rsync files output: '. $ol);
            // }
            $this->log->log('rsync files end');
        }

        // sync images and files
        foreach ( [ 'images', 'file2s', 'files', ] as $dirname ) {
            $target = $this->path->tempPublicDir().DS.$dirname;
            if (file_exists($target)) {
                $this->syncFiles($target, $dirname);
            }
        }

        // move files in public dir
        foreach ($this->handle->scan($this->path->tempPublicDir()) as $filename) {

        	if ( $filename === 'images' || $filename === 'file2s' || $filename === 'files' ) {
                continue;
            }

            $from = $this->path->tempPublicDir().DS.$filename;
            $to   = $this->path->publicDir().DS.$filename;

            if($this->useWork) {
                $to   = $this->path->publicWorkDir().DS.$filename;
            }

            $this->handle->move($from, $to);
        }

        // move files in files dir
        $target = $this->path->tempFilesDir().DS.$this->publishType;
        foreach ($this->handle->scan($target) as $filename) {

            if ($filename === 'setting' && $this->mergeSettingFile($target)) {
                continue;
            }

            $from = $target.DS.$filename;
            $to   = $this->path->filesDir().DS.$this->publishType.DS.$filename;

            if($this->useWork) {
                $to   = $this->path->filesDir().DS.$this->publishType.self::$colorSetting['filesWorkColor'].DS.$filename;
            }

            $this->handle->move($from, $to);
            $this->log->log( "move {$from} {$to}" ) ;
        }

        // robots.txt
        $this->updateRobotsTxt();

        if($this->useWork) {
            // 公開領域のシンボリックリンク切り替え
            $mod_link_cmd = [];
            $mod_link_cmd[] = "cd " . dirname($public_cur_dir);
            $mod_link_cmd[] = "rm " . basename($this->path->publicDir());
            $mod_link_cmd[] = "ln -s " .  basename($public_work_dir) . " " . basename($this->path->publicDir());

            $this->log->log('command to change public-link: '. implode(" && ", $mod_link_cmd));

            exec(implode(" && ", $mod_link_cmd), $output, $return_var);
            if($return_var != 0) {
                throw new Exception("公開領域のシンボリックリンク切り替えに失敗しました。");
            }

            // files領域のシンボリックリンク切り替え
            $mod_link_cmd = [];
            $mod_link_cmd[] = "cd " . dirname($files_cur_dir);
            $mod_link_cmd[] = "rm " . $this->publishType;
            $mod_link_cmd[] = "ln -s " . basename($files_work_dir) . " " . $this->publishType;

            $this->log->log('command to change files-link: '. implode(" && ", $mod_link_cmd));

            exec(implode(" && ", $mod_link_cmd), $output, $return_var);
            if($return_var != 0) {
                throw new Exception("非公開領域のシンボリックリンク切り替えに失敗しました。");
            }
        }

        // delete files
        $this->after();
    	$this->log->log( "run() finish" ) ;
        
        return true;
    }

    private function updateRobotsTxt() {
    	$this->log->log( "updateRobotsTxt() start" ) ;
    	$publicDir = $this->path->publicDir();

        if($this->useWork) {
            $publicDir = $this->path->publicWorkDir();
        }

        $domain = $this->getPublishType();
        if ($domain == 'public') {
            $domain = 'www';
        }
        $domain .= '.' . basename($publicDir);
        $robotsTxtName = $publicDir.DS.'robots.txt';
        $xmls = glob($publicDir.DS.'*.xml');
        $bxmls = [];
        $protocol = 'https';
        foreach ($xmls as $xml) {
            if (preg_match('/sitemap_b(_\d+)?\.xml$/', $xml)) {
                $data = "Sitemap:{$protocol}://{$domain}/".basename($xml)."\n";
                file_put_contents($robotsTxtName, $data, FILE_APPEND);
            }
        }
    	$this->log->log( "updateRobotsTxt() finish" ) ;
    }

    private function unzip() {

    	$this->log->log( "unzip() start" ) ;

        $zipPathArray = [];

        // search zip file
        $dirctory = $this->handle->scan($this->path->homeDir().DS.'files'.DS.'temp');
        foreach ($dirctory as $filename) {
            $basename = basename($filename);
            if (strtolower((new SplFileInfo($basename))->getExtension()) === 'zip') {
                $zipPath = $this->path->homeDir().DS.'files'.DS.'temp'.DS.$basename;
        
        // 0byte
        if (filesize($zipPath) === 0) { 
        	throw new Exception("zipファイルの容量が0byteです。");
        }
                $zipPathArray[] = $zipPath;
           }
        }

        // none
        if(count($zipPathArray) == 0) {
            throw new Exception("zipファイルがありません。");
        }

        foreach($zipPathArray as $zipPath) {
            $this->log->log( "unzip {$zipPath}" );

        // unzip
        exec("unzip -o {$zipPath} -d ".dirname($zipPath));

        // remove self
        exec("rm -rf {$zipPath}");
        }

        // permission
        exec("chmod -R 755 ".dirname($zipPath));

    	$this->log->log( "unzip() finish" ) ;
        return true;
    }

    private function getPublicDirname() {

    	$this->log->log( "getPublicDirname() start" ) ;

    	foreach ($this->handle->scan($this->path->tempDir()) as $name) {

            $basename = basename($name);

            if (strtolower((new SplFileInfo($basename))->getExtension()) === 'zip' || $basename === 'files') {
                continue;
            };

            return $basename;
        };
    	$this->log->log( "getPublicDirname() finish" ) ;
    }

    private function getPublishType() {

    	$this->log->log( "getPublishType() start" ) ;
    	
    	if (strpos($this->path->publicDirname, 'substitute.', 0) === 0) {
            return 'substitute';
        }
        if (strpos($this->path->publicDirname, 'test.', 0) === 0) {
            return 'test';
        }

    	$this->log->log( "getPublishType() finish" ) ;
        
    	return 'public';
    }

    private function syncFiles($target, $dirname) {

        $this->log->log( "start syncFiles(" . $target . ")" ) ;
    	$txtFile = $target.DS.'delete.txt';

        // delete
        if (file_exists($txtFile)) {

            $content = file_get_contents($txtFile);

            // all
            if ($content === 'all') {
            	$this->log->log( "delete all" ) ;
                $path = $this->path->publicDir().DS.$dirname;

                if($this->useWork) {
                    $path = $this->path->publicWorkDir().DS.$dirname;
                }

                exec("find {$path} -type f -print0 | xargs 0 rm -rf {} \;", $res1, $res2);
                exec("rm -rf {$path}", $res1, $res2);
                unlink($txtFile);

                rename($target, $path);
                $this->log->log( "end syncFiles()" ) ;
                return;
            }

            // individually
            if(strlen($content)) {
                $list = explode(',', $content);
                if (count($list) > 0) {
                	$this->deleteIndividually($list, $dirname);
                }
            }

            // txt file
            unlink($txtFile);
        }

        // move
        $zipList = [];
        foreach ($this->handle->scan($target) as $filename) {

            $from = $target.DS.$filename;
            $to   = $this->path->publicDir().DS.$dirname.DS.$filename;

            if($this->useWork) {
                $to = $this->path->publicWorkDir().DS.$dirname.DS.$filename;
            }

            $this->handle->move($from, $to);

            if(preg_match("/zip$/", $to)) {
                $zipList[] = $to;
            }
        }

        if(count($zipList)) {
            foreach($zipList as $zipPath) {
                $this->log->log( "unzip " . $zipPath ) ;
                exec("unzip -o {$zipPath} -d ".dirname($zipPath));
                unlink($zipPath);
            }
        }
        $this->log->log( "end syncFiles()" ) ;
    }

    private function deleteIndividually($list, $dirname) {

    	$this->log->log( "deleteIndividually() start" ) ;
    	
    	$target = $this->path->publicDir().DS.$dirname;

        if($this->useWork) {
    	    $target = $this->path->publicWorkDir().DS.$dirname;
        }

        foreach ($this->handle->scan($target) as $filename) {

            $path = $target.DS.$filename;

            if (!in_array(pathinfo($path)['filename'], $list)) {
                continue;
            };
            $this->log->log( "delete {$path}" ) ;
            
            if (is_dir($path)) {
                $this->handle->rmDir($path);
            }
            elseif (is_file($path)) {
                unlink($path);
            }
        }
        
    	$this->log->log( "deleteIndividually() finish" ) ;
    }

    private function mergeSettingFile($temp_path) {

    	$this->log->log( "mergeSettingFile() start" ) ;
    	
    	// ディレクトリ名
        $dirname = 'setting';

        // 問い合わせページID一覧
        $list_filename = 'contact_page_list.txt';

        // アップロードされたsettingディレクトリのパス
        $temp = $temp_path.DS.$dirname;

        // 本番のsettingディレクトリのパス
        $public = $this->path->filesDir().DS.$this->publishType.DS.$dirname;

        if($this->useWork) {
            $public = $this->path->filesDir().DS.$this->publishType.self::$colorSetting['filesWorkColor'].DS.$dirname;
        }

        // 問い合わせページID一覧ファイルパス
        $list_file = $temp.DS.$list_filename;

        // 一覧ファイルあれば
        if (file_exists($list_file)) {

            // 一覧を配列で格納
            $contact_list = explode(',', file_get_contents($list_file));

            // 本番のsettingファイル内を検索
            foreach ($this->handle->scan($public) as $filename) {

                // ファイルへのパス
                $filepath = $public.DS.$filename;

                // contact_{page_name}_{page_id}.ini ファイル探す
                $pre = 'contact_';
                // $suf = '.ini';

                if (strpos($filename, $pre, 0) !== 0) {
                    continue;
                }

                // ファイル名からページIDを取得
                $basemame = explode('.', $filename)[0];
                $page_id  = end(explode('_', $basemame));

                // contact_{page_id}.ini 以外はスルー
                if (!is_numeric($page_id)) {
                    continue;
                }

                // 公開中でなければ削除
                if (!in_array($page_id, $contact_list)) {
                    unlink($filepath);
                }
            }

            // 一覧ファイル削除
            unlink($list_file);

            // アップロードされたsetting配下のファイルをひとつずつ本番に移動
            foreach ($this->handle->scan($temp) as $filename) {

                $from = $temp.DS.$filename;
                $to   = $public.DS.$filename;
                $this->handle->move($from, $to);
            }

            $this->log->log( "mergeSettingFile() finish" ) ;
            
            return true;
        }

        // 一覧ファイルなければfalse
//         return false;
        throw new Exception("一覧ファイルがありません。");
    }

    private function after() {

        $this->log->log( "after() start" ) ;
        
        $this->handle->rmDir($this->path->tempDir());
        unlink(basename(__FILE__));
        
        $this->log->log( "after() finish" ) ;
    }

}

class Path {

    public $publicDirname;

    public function homeDir() {

        return dirname(dirname(__FILE__));
    }

    public function publicDir() {

        return $this->homeDir().DS.$this->publicDirname;
    }

    public function publicCurDir() {

        $public_dir = $this->publicDir();
        return $public_dir . Sync::$colorSetting['publicCurColor'];
    }

    public function publicWorkDir() {

        $public_dir = $this->publicDir();
        return $public_dir . Sync::$colorSetting['publicWorkColor'];
    }

    public function filesDir() {

        return $this->homeDir().DS.'files';
    }

    public function tempDir() {

        return $this->filesDir().DS.'temp';
    }

    public function tempFilesDir() {

        return $this->tempDir().DS.'files';
    }

    public function tempPublicDir() {

        return $this->tempDir().DS.$this->publicDirname;
    }
}

class Handler {

    public function scan($dir) {

        $list = scandir($dir);
        foreach ($list as $i => $dirname) {
            if ($dirname === '.' || $dirname === '..') {
                unset($list[$i]);
            }
        }
        return $list;
    }

    public function move($from, $to) {

        if (file_exists($to)) {

            if (is_dir($to)) {
                $this->rmDir($to);
            }

            if (is_file($to)) {
                unlink($to);
            }
        }

        $this->mkdir(dirname($to));
        rename($from, $to);
    }

    public function mkdir($path) {

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    public function rmDir($path) {

        exec("rm -rf {$path}");
    }
}

class DLog {

	private $file = null;
	public function __construct() {
		$path = '../files/log/';
		if (!file_exists($path)) {
			mkdir($path);
		}
		$date     = date('ymd');
		$filename = "debug.log.{$date}";
		$this->file = $path . $filename;
		
		// 当日、前日以外のログは削除
		$yesterday = date("ymd",strtotime("-1 day"));
		$excludeFile = [ $filename, "debug.log.{$yesterday}"];
		$res_dir = opendir( $path );
		while( $file_name = readdir( $res_dir ) ){
			if(is_file($path.'/'.$file_name) && !in_array($file_name, $excludeFile)) {
				unlink($path.'/'.$file_name);
			}
		}
	}

	public function log($msg) {
		$datetime = date("Y-m-d H:i:s");
		$body = "{$datetime},{$msg}\n";
		error_log($body, 3, $this->file);
	}

	public function error($msg, Exception $e = null) {
		$datetime = date("Y-m-d H:i:s");
		$body = "{$datetime},{$msg}\n";
		error_log($body, 3, $this->file);
		if ($e) {
		    error_log('[message]'.$e->getMessage() . "\n", 3, $this->file);
		    error_log('[file]'.$e->getFile() . "\n", 3, $this->file);
		    error_log('[line]'.$e->getLine() . "\n", 3, $this->file);
		    error_log('[trace]'.$e->getTraceAsString() . "\n", 3, $this->file);
		}
	}
}

/**
 * 
 */
class CheckIP
{
    public function __construct()
    {
        $path = APPLICATION_PATH.'/../files/access/ips.ini';
        $config = parse_ini_file($path,true);
        $ips = $config['access']['ips'];
        $baseurl = $config['tracking']['baseurl'];
        $data_tracking  = array();
        $data_tracking['http_host']         = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'N/A';
        $data_tracking['http_user_agent']   = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A';
        $data_tracking['remote_addr']       = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A';
        $data_tracking['remote_addr_net']   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A';

        // ELB経由アクセス(各種makeサーバ用)用処理
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $data_tracking['remote_addr']       = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $data_tracking['remote_addr_net']   = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        $data_tracking['server_addr']       = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A';
        $data_tracking['server_port']       = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'N/A';
        $data_tracking['http_referer']      = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A';
        $data_tracking['com_id']            =   $config['tracking']['com_id'];
        $data_tracking['api_key']           =   $config['tracking']['api_key'];
       
        if($this->checkAccess($data_tracking['remote_addr_net'],$ips)){
            $data_tracking['access']='true';
            $this->saveLogServer($data_tracking, $baseurl);
        }
        else{
            $data_tracking['access']='false';
            $this->saveLogServer($data_tracking, $baseurl);
            header('HTTP/1.1 403 Forbidden');
            die();
        }
    }

    private function saveLogServer($data_tracking, $baseurl){
        $postdata = http_build_query(
            $data_tracking
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context  = stream_context_create($opts);
        file_get_contents($baseurl,false,$context);
    }


    private function ipCIDRCheck ($ip, $cidr) {
        list ($net, $mask) = split ("/", $cidr);
        $ip_net = ip2long ($net);
        $ip_mask = ~((1 << (32 - $mask)) - 1);
        $ip_ip = ip2long ($ip);
        $ip_ip_net = $ip_ip & $ip_mask;
        return ($ip_ip_net == $ip_net);
    }

    private function checkAccess(&$ip,$access){
        foreach ($access as $ipnet) {
            if($this->ipCIDRCheck($ip,$ipnet)){
                $ip = $ipnet;
                return true;
            }
        }
        $ip.='/32';
        return false;
    }
}

; ?>
