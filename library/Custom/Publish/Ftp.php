<?php
namespace Library\Custom\Publish;

use App\Repositories\Company\CompanyRepositoryInterface;

class Ftp {

    private $company;

    private $publishType;

    private $ftpServer;
    private $ftpPort;
    private $ftpUserName;
    private $ftpUserPass;
    private $ftpPasv;
    private $timeout = 300;

    private $stream;
    public  $isLogin;

    private $remoteBasePath;
    private $remoteFilesPath;
    private $remotePublicPath;
    private $remoteTempPath;

    private $executionPath;

    public function __construct($hpId, $publishType) {

    	$this->setPublishType($publishType);
    	$this->setCompany(\App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($hpId));

        $this->setServerDetail();
        $this->setRemotePath();
        $this->setLocalPath();

    }

    // gettter, setter

    /**
     * gmoサーバーの情報をセット
     *
     */
    private function setServerDetail() {


        $this->setFtpServer($this->getCompany()->ftp_server_name);
        $this->setFtpPort($this->getCompany()->ftp_server_port);
        $this->setFtpUserName($this->getCompany()->ftp_user_id);
        $this->setFtpUserPass($this->getCompany()->ftp_password);
        $this->setTimeout($this->timeout);

        // なぜかフラグが逆に定義されいました。なぜ？なぜなの？？？
        // 無効:1
        // 有効:0
        $this->setFtpPasv(true);
        if ($this->getCompany()->ftp_pasv_flg == config('constants.ftp_pasv_mode.INVALID')) {
            $this->setFtpPasv(false);
        }
    }

    /**
     * gmoサーバーのパスをセット
     *
     */
    private function setRemotePath() {

        $prefix = $this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC') ? '' : self::getPublishName($this->getPublishType()).'.';

        $this->setRemotePublicPath($prefix.$this->getCompany()->ftp_directory);
        $this->setRemoteFilesPath('files/'.self::getPublishName($this->getPublishType()));
        $this->setRemoteTempPath('files/temp');
        $this->setRemoteBasePath($this->getCompany()->full_path);
    }

    /**
     * cmsサーバーのパスをセット
     *
     */
    private function setLocalPath() {

        $ds = DIRECTORY_SEPARATOR;
        $this->setExecutionPath(storage_path().$ds.'data'.$ds.'publish'.$ds.'execution');
    }

    public static function getPublishName($publishType) {

        switch ($publishType) {
            case config('constants.publish_type.TYPE_PUBLIC'):
                return 'public';
            case config('constants.publish_type.TYPE_TESTSITE'):
                return 'test';
            case config('constants.publish_type.TYPE_SUBSTITUTE'):
                return 'substitute';
            case config('constants.publish_type.TYPE_PREVIEW'):
                return 'preview';
        }
    }

    public function getCompany() {
        return $this->company;
    }

    private function setCompany($company) {

        $this->company = $company;
        
        // ATHOME_HP_DEV-2197	本番以外の公開の時に必要
		$contractType	= $this->company->contract_type		;
		$isPublic		= ( $this->publishType == config('constants.publish_type.TYPE_PUBLIC')			) ;
		$isDemoSite		= ( $contractType == config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')	) ;
		if ( !$isPublic || $isDemoSite )
		{
			$config 		= getConfigs('sales_demo')		                        ;
			$demoDomain		= $config->demo->domain									;
			$isDemo			= ""													;
			$ftpPassWord	= $this->company->ftp_password							;
			$ftpUserName	= $this->company->ftp_user_id							;
			if( $contractType == config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') )
			{	// 本契約なら、デモサイトでは無い扱いにする
        		$isDemo			= "isDemo=false&"									;
        		$ftpUserName	= $this->company->member_no							;
        	}
        	$api			= "http://api.apache.{$demoDomain}/addUser.php"			;
        	$api			= "{$api}?{$isDemo}user={$ftpUserName}"					;
        	$api			= "{$api}&pass={$ftpPassWord}&key={$config->api->key}"	;
        	file_get_contents( $api )												;
        	$this->company->ftp_server_name	= "ftp.{$demoDomain}"					;
        	$this->company->domain			= "{$ftpUserName}.{$demoDomain}"		;
        	$this->company->ftp_directory	= "{$ftpUserName}.{$demoDomain}"		;
        	$this->company->ftp_user_id		= "{$ftpUserName}"						;
        }
    }

    protected function getPublishType() {
        return $this->publishType;
    }

    private function setPublishType($publishType) {

        $this->publishType = $publishType;
    }

    protected function getStream() {
        return $this->stream;
    }

    protected function setStream($stream) {
        $this->stream = $stream;
    }

    protected function getFtpServer() {
        return $this->ftpServer;
    }

    private function setFtpServer($ftpServer) {
        $this->ftpServer = $ftpServer;
    }

    protected function getFtpPort() {
        return $this->ftpPort;
    }

    private function setFtpPort($ftpPort) {
        $this->ftpPort = $ftpPort;
    }

    protected function getFtpUserName() {
        return $this->ftpUserName;
    }

    private function setFtpUserName($ftpUserName) {
        $this->ftpUserName = $ftpUserName;
    }

    protected function getFtpUserPass() {
        return $this->ftpUserPass;
    }

    private function setFtpUserPass($ftpUserPass) {
        $this->ftpUserPass = $ftpUserPass;
    }

    protected function getRemoteBasePath() {
        return $this->remoteBasePath;
    }

    protected function setRemoteBasePath($remoteBasePath) {
        $this->remoteBasePath = $remoteBasePath;
    }

    protected function getRemoteFilesPath() {
        return $this->remoteFilesPath;
    }

    private function setRemoteFilesPath($remoteFilesDir) {
        $this->remoteFilesPath = $remoteFilesDir;
    }

    protected function getRemotePublicPath() {
        return $this->remotePublicPath;
    }

    private function setRemotePublicPath($remotePublicDir) {
        $this->remotePublicPath = $remotePublicDir;
    }

    protected function getRemoteTempPath() {
        return $this->remoteTempPath;
    }

    private function setRemoteTempPath($remoteTempDir) {
        $this->remoteTempPath = $remoteTempDir;
    }

    protected function getFtpPasv() {
        return $this->ftpPasv;
    }

    private function setFtpPasv($ftpPasv) {
        $this->ftpPasv = $ftpPasv;
    }

    protected function getTimeout() {
        return $this->timeout;
    }

    private function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    protected function getExecutionPath() {
        return $this->executionPath;
    }

    private function setExecutionPath($path) {
        $this->executionPath = $path;
    }

    // end of getter, setter

    /**
     * zipファイルをアップロード
     *
     * @param $localFile
     * @throws Exception
     */
    public function uploadZip($localFile) {

        $remoteFile = $this->getRemoteTempPath().'/'.basename($localFile);
        if (!$this->uploadRecursive($localFile, $remoteFile)) {
            return array(false, $remoteFile);
        }
        return array(true, null);
        }

    /**
     * アップロードしたファイルのサイズを取得する
     */
    public function getSize($remoteFile) {
        return ftp_size($this->getStream(), $remoteFile);
    }

    /**
     * フルパス取得
     */
    public function fullPath($publishType) {

        $path = $this->execute('fullpath.php', $publishType);

        if (!$path) {
            $msg = 'パスの取得に失敗しました。';
            throw new \Exception($msg);
        }

        $path = dirname(dirname($path));

        $data = array(
            'full_path' => $path,
        );
        \App::make(CompanyRepositoryInterface::class)->update($this->getCompany()->id, $data);
        $this->setRemoteBasePath($path);
    }

    /**
     * zipファイルを本番に適用
     *
     * @param $publishType
     * @throws Exception
     */
    public function commit($publishType) {

        $this->makeIpsIni($publishType);
        
        if (!$this->execute('commit.php', $publishType)) {
            $msg = '同期に失敗しました。';
            throw new \Exception($msg);
        }

        if ( $this->downloadPublic( "commit.php", "/dev/null" ) )
        {
        	// 「commit.php」ファイルが残っていれば削除(保険)
        	$this->deletePublic( [ "commit.php" ] )	;
        }
        
        if ( $this->downloadPublic( ".end", "/dev/null" ) )
        {
        	// UNZIP成功したので、「.end」ファイルを削除
        	$this->deletePublic( [ ".end" ] )	;
        }
        else
        {
            throw new \Exception( "圧縮ファイルの解凍に失敗しました。" )	;
        }
    }


    private function makeIpsIni($publishType) {
        $ds = DIRECTORY_SEPARATOR;

        $fileName = 'files/access/ips.ini';

        // com_id
        $com_id = $this->company->id;
        if($publishType != config('constants.publish_type.TYPE_SUBSTITUTE')){
            $hp = $this->company->getCurrentHp();
        }
        else{
            $hp = $this->company->getCurrentCreatorHp();
        }
        // api_key
        $api_key = '"'.$this->company->fetchCompnayAccountRow()->api_key.'"';

        $config_api = getConfigs('api');

        $config_commit = getConfigs('commit');
        // baseurl
        $baseurl = 'http://'.$config_api->api->domain.'/api/commission/tracking';
        
        foreach ($config_commit->commit->ips as $value) {
            $array_ips[] = 'ips[] = '.$value;
        }
        $ips = implode(PHP_EOL, $array_ips);
        $contents = <<<EOD
[access]
$ips
[tracking]
baseurl = $baseurl
com_id = $com_id
api_key = $api_key
EOD;
        $ds = DIRECTORY_SEPARATOR;
        $temp_folder = storage_path().$ds.'data'.$ds.'html'.$ds.'tmp'.$ds.$hp->id;    
        if(!is_dir($temp_folder)){
            mkdir($temp_folder,0777,true);
        }
        $fp = fopen( $temp_folder."/ips.ini", "w");
        fwrite($fp,$contents);
        fclose($fp);
        if (!$this->uploadRecursive($temp_folder."/ips.ini",$fileName,true)) {
            throw new \Exception('ファイルの展開に失敗しました。');
        };
    }

    private function execute($uploadedFile, $publishType) {

        $ds = DIRECTORY_SEPARATOR;
        $prefix = Render\AbstractRender::prefix($publishType);

        // htaccessファイルを更新する
        $this->updateHtaccess($publishType);

        //  アップロード
        $local = $this->getExecutionPath().$ds.$uploadedFile;
        $remote = $prefix.$this->getCompany()->domain.'/'.$uploadedFile;
        if (!$this->uploadRecursive($local, $remote)) {
            throw new \Exception('ファイルの展開に失敗しました。');
        };

        // 実行
        $url = 'http://'.$prefix.$this->getCompany()->domain.'/'.$uploadedFile;
        /*
         * uploadedFileが大きくてFTPでSocketタイムアウトになるので一時的にタイムアウト時間を変更
         */
        // 後で戻せるように設定を保持っとく
        $org_timeout = ini_get('default_socket_timeout');

        // 秒以上かかったらタイムアウトにする
        ini_set('default_socket_timeout', 900);
        //file_get_contents関数でデータを取得
        if($res = file_get_contents($url)){
            //ここにデータ取得が成功した時の処理
        	$resObj = json_decode($res, false);
        	if ($resObj->success) {
        		$result = $resObj->result;
        	} else {
        		if (isset($resObj->error)) {
        			error_log(mb_convert_encoding(print_r($resObj->error,1), 'UTF-8', 'UTF-8,eucJP-win,SJIS-win'));
        		} else {
        			error_log(mb_convert_encoding(print_r($resObj,1), 'UTF-8', 'UTF-8,eucJP-win,SJIS-win'));
        		}
        		throw new \Exception("GMOサーバでの展開に失敗しました。");
        	}
        }else{
            //エラー処理
            if(count($http_response_header) > 0){
                //「$http_response_header[0]」にはステータスコードがセットされているのでそれを取得
                $status_code = explode(' ', $http_response_header[0]);  //「$status_code[1]」にステータスコードの数字のみが入る
                //エラーの判別
                switch($status_code[1]){
                    //404エラーの場合
                    case 404:
                        throw new \Exception("指定したページが見つかりませんでした");
                        break; 
                    //500エラーの場合
                    case 500:
                        throw new \Exception("指定したページがあるサーバーにエラーがあります");
                        break;
                    //その他のエラーの場合
                    default:
                        $this->deletePublic(['../files/access/ips.ini','commit.php']);
                        throw new \Exception("何らかのエラーによって指定したページのデータを取得できませんでした");
                }
            }else{
                //タイムアウトの場合 or 存在しないドメインだった場合
                throw new \Exception("タイムアウトエラー or ドメインが間違っています");
            }
        }
        //設定を戻す
        ini_set('default_socket_timeout', $org_timeout);
        return $result;
    }


    private function updateHtaccess($publishType) {

        $htaccess = '.htaccess';
        $prefix = Render\AbstractRender::prefix($publishType);

		$contractType	= $this->company->contract_type		;
		$isPublic		= ( $publishType	== config('constants.publish_type.TYPE_PUBLIC')				) ;
		$isDemoSite		= ( $contractType	== config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')	) ;
        // デモアカウントのサイトを除く本番環境の本番サイト以外は、ベーシック認証がかかるため、公開処理中は.htaccessを削除
		if ( !$isPublic || $isDemoSite )
        {
            // .htaccess一旦削除
            $remote = $prefix.$this->getCompany()->domain.'/'.$htaccess;
            if (ftp_size($this->getStream(), $remote) != -1) {
                ftp_delete($this->getStream(), $remote);
            };
        }
    }

    /**
     * ログイン
     *
     * @throws Exception
     */
    public function login() {

        $this->setStream(ftp_connect($this->getFtpServer(), $this->getFtpPort(), $this->getTimeout()));

        if (!ftp_login($this->getStream(), $this->getFtpUserName(), $this->getFtpUserPass())) {

            $msg = 'ログインに失敗しました。';
            throw new \Exception($msg);
        };

        $this->isLogin = true;
    }

    /**
     * ログアウト
     *
     * @throws Exception
     */
    public function close($force=false) {

        if (!ftp_close($this->getStream())) {

            if(!$force) {
                $msg = 'ログアウトに失敗しました。';
                throw new \Exception($msg);
            }
        }

        $this->isLogin = false;
    }

    /**
     * アップロード
     * - アップロード先のディレクトリがない場合は作成
     *
     * @param      $localPath
     * @param      $remotePath
     * @param bool $deleteLocalFile
     * @return bool
     */
    protected function uploadRecursive($localPath, $remotePath, $deleteLocalFile = false) {

        $this->mkdirWithParent(dirname($remotePath));
        ftp_pasv($this->getStream(), $this->getFtpPasv());
        $bool = ftp_put($this->getStream(), $remotePath, $localPath, $this->getTransfarMode($localPath));
        if ($deleteLocalFile) {
            unlink($localPath);
        }
        return $bool;
    }

    /**
     * 公開領域にアップロードする
     * @param  string $localPath   ローカルファイルパス
     * @param  string $remotePath  公開領域からの相対パス
     * @return boolean
     */
    public function uploadToPublicRecursive($localPath, $remotePath) {
        $prefix = Render\AbstractRender::prefix($this->publishType);
        $remotePath = $prefix.$this->getCompany()->domain.'/'.$remotePath;
        return $this->uploadRecursive($localPath, $remotePath, false);
    }

    /**
     * 公開領域のファイル一覧を取得する
     * @param  string $remotePath  公開領域からの相対パス
     * @return array | boolean
     */
    public function nlistPublic($path) {
        $prefix = Render\AbstractRender::prefix($this->publishType);
        $remotePath = '/'.$prefix.$this->getCompany()->domain.'/'.$path;
        ftp_pasv($this->getStream(), $this->getFtpPasv());
        return ftp_nlist($this->getStream(), $remotePath);
    }

    /**
     * 公開領域のファイルを削除する
     * @param  string|array $remotePath  公開領域からの相対パス
     * @return array | boolean
     */
    public function deletePublic($paths) {
        $paths = (array) $paths;
        $prefix = Render\AbstractRender::prefix($this->publishType);
        ftp_pasv($this->getStream(), $this->getFtpPasv());
        foreach ($paths as $path) {
            $remotePath = $prefix.$this->getCompany()->domain.'/'.$path;
            ftp_delete($this->getStream(), $remotePath);
        }
    }

    /**
     * 公開領域のファイルを取得する
     * @param  string $remotePath  公開領域からの相対パス
     * @param  string $localPath   ローカルファイルパス
     * @return array | boolean
     */
    public function downloadPublic($remotePath, $localPath) {
        $prefix = Render\AbstractRender::prefix($this->publishType);
        $remotePath = $prefix.$this->getCompany()->domain.'/'.$remotePath;
        ftp_pasv($this->getStream(), $this->getFtpPasv());
        return @ftp_get($this->getStream(), $localPath, $remotePath,FTP_BINARY);
    }

    /**
     * ディレクトリ判定
     *
     * @param $dir
     * @return bool
     */
    private function isDir($dir) {

        if (@ftp_chdir($this->getStream(), $dir)) {
            $this->returnHomeDir();
            return true;
        }
        return false;
    }

    /**
     * ホームディレクトリへ移動
     */
    private function returnHomeDir() {

        while (ftp_pwd($this->getStream()) !== '/') {
            ftp_chdir($this->getStream(), '..');
        }
    }

    /**
     * 転送モードを取得
     *
     * @param $localPath
     * @return int
     */
    private function getTransfarMode($localPath) {

        if (exif_imagetype($localPath)) {
            return FTP_BINARY;
        };

        $path_parts = pathinfo($localPath);

        switch (strtolower($path_parts['extension'])) {
            case 'zip':
                return FTP_BINARY;
            case 'php':
            default:
                return FTP_ASCII;
        }
    }

    /**
     * 親フォルダも含めてディレクトリ作成
     *
     * @param $dir
     */
    private function mkdirWithParent($dir) {

        if ($this->isDir($dir)) {
            return;
        }

        $dirs = explode(DIRECTORY_SEPARATOR, $dir);
        $parentDir = '';
        foreach ($dirs as $i => $dir) {

            $parentDir .= $parentDir ? '/'.$dirs[$i] : $dirs[$i];
            if (!$this->isDir($parentDir)) {
                if (!$this->mkdir($parentDir)) {
                	throw new \Exception('FTPに失敗しました。');
                }
            };
        }
    }

    /**
     * ディレクトリ作成
     *
     * @param $dir
     * @return string
     */
    private function mkdir($dir) {

        return ftp_mkdir($this->getStream(), $dir);
    }

    /**
     * ディレクトリ配下のものを全部消す
     *
     * @param $dir
     */
    public function allDelete($dir) {

        $pre_dir = ftp_pwd($this->getStream());

        //HTMLが置かれているディレクトリに移動
        ftp_chdir($this->getStream(), $dir);

        //一覧取得
        $list = ftp_rawlist($this->getStream(), "./");

        foreach($list as $key => $val) {

            $child = preg_split("/\s+/", $val);
            if($child[8] == "." || $child[8] == "..") continue;

            if($child[0][0] === "d") {
                ftp_rmdir($this->getStream(), $child[8]);
            }else{
                ftp_delete($this->getStream(), $child[8]);
            }
        }
        ftp_chdir($this->getStream(), $pre_dir);

    }

    /**
     * ディレクトリ配下および自身のディレクトリ(rmdirSelf=true時)を削除する
     *
     * @param $dir
     * @param $rmdirSelf
     */
    public function allRemove($dir, $rmdirSelf=false) {

        $pre_dir = ftp_pwd($this->getStream());

        ftp_pasv($this->getStream(), $this->getFtpPasv());

		if(@ftp_chdir($this->getStream(), $pre_dir . "/" . $dir) == true) {
			$list = ftp_rawlist($this->getStream(), "-a ./");

			foreach($list as $val) {
				$child = preg_split("/\s+/", $val);

				if($child[8] == "." || $child[8] == "..") continue;

				if($child[0][0] === "d") {
					$this->allRemove($child[8], true);
				} else {
					ftp_delete($this->getStream(), $child[8]);
				}
			}
			ftp_chdir($this->getStream(), $pre_dir);
		}

		if($rmdirSelf) {
			ftp_rmdir($this->getStream(), $dir);
		}

        return;
    }
}
?>
