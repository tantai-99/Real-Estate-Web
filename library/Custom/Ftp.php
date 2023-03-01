<?php

namespace Library\Custom;

use App\Exceptions\CustomException;

class Ftp {
	
	private $_conn;
	
	/**
	 * @param string host
	 * @param int port
	 * @param int timeout
	 */
	public function __construct($host, $port = 21, $timeout = 90) {
		$this->_conn = ftp_connect($host, $port, $timeout);
		if ($this->_conn === false) {
			throw new CustomException('ftp error: ftp_connect');
		}
	}
	
	/**
	 * ftp_
	 * @param string $name
	 * @param array $arguments
	 * @throws CustomException
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		$func = 'ftp_' . $name;
		array_unshift($arguments, $this->_conn);
		$ret = @call_user_func_array($func, $arguments);
		if ($ret === false) {
			throw new CustomException('ftp error: ' . $func);
		}
		return $ret;
	}
	
	/**
	 * アップロードする
	 * @param string $remote_path
	 * @param string $local_path
	 */
	public function uploadDir($remote_path, $local_path) {
		$local_path = realpath($local_path);
		
		if (Util::isEmpty($remote_path)) {
			$remote_path = './';
		}
		
		$this->mkdirIfNotExists($remote_path, true);
		
		$dh = opendir($local_path);
		if (!$dh) {
			throw new CustomException('アップロード対象ディレクトリの読み込みに失敗しました');
		}
		
		while(false !== ($filename = readdir($dh))) {
			if ($filename === '.' || $filename === '..') {
				continue;
			}
			
			$local = $local_path . DIRECTORY_SEPARATOR . $filename;
			$remote = rtrim($remote_path, '/') . '/' . $filename;
			if (is_dir($local)) {
				$this->uploadDir($remote, $local);
			}
			else if (is_file($local)) {
				$this->fput($remote, $local);
			}
		}
		
		closedir($dh);
	}
	
	/**
	 * ファイルアップロード
	 */
	public function uploadFile($remote_path, $local_path, $mode = FTP_BINARY) {
		$remote_dir = $this->_dirname($remote_path);
		$this->mkdirIfNotExists($remote_dir);
		$this->fput($remote_path, $local_path, $mode);
	}
	
	/**
	 * ファイルアップロード
	 * @param string $remote_path
	 * @param string|resource $local_path ローカルファイル名またはファイルポインタ
	 * @param string $mode 転送モード。FTP_ASCII または FTP_BINARY のどちらか(デフォルトはFTP_BINARY)
	 */
	public function fput($remote_path, $local_path, $mode = FTP_BINARY) {
		if (is_resource($local_path)) {
			$fp = $local_path;
		}
		else {
			$fp = fopen($local_path, 'r');
			if (!$fp) {
				throw new CustomException('アップロード対象ファイルの読み込みに失敗しました');
			}
		}
		
		$this->__call('fput', array($remote_path, $fp, $mode));
		
		if (!is_resource($local_path)) {
			fclose($fp);
		}
	}
	
	/**
	 * ディレクトリが無い場合にディレクトリを作成する
	 * @param string $remote_dir
	 * @param boolean $recursive
	 */
	public function mkdirIfNotExists($remote_dir, $recursive = false) {
		if (!$this->isDir($remote_dir)) {
			$this->mkdir($remote_dir, $recursive);
		}
	}
	
	/**
	 * ディレクトリを作成する
	 * @param string $remote_dir
	 * @param boolean $recursive
	 */
	public function mkdir($remote_dir, $recursive = false) {
		if (!$recursive) {
			return $this->__call('mkdir', array($remote_dir));
		}
		
		$parent_dir = $this->_dirname($remote_dir);
		if (!$this->isDir($parent_dir)) {
			$this->mkdir($parent_dir, true);
		}
		$this->mkdir($remote_dir);
	}
	
	/**
	 * ディレクトリが無い場合にディレクトリを作成する
	 * @param string $remote_dir
	 */
	public function rmdirIfExists($remote_dir) {
		if (!$this->isDir($remote_dir)) {
			$this->rmdir($remote_dir);
		}
	}
	
	/**
	 * ディレクトリを削除する
	 * @param string $remote_dir
	 */
	public function rmdir($remote_dir) {
		foreach ($this->nlist('-a ' . $remote_dir) as $path) {
			$name = basename($path);
			if ($name === '.' || $name === '..') {
				continue;
			}
			
			if ($this->isDir($path)) {
				$this->rmdir($path);
			}
			else {
				$this->delete($path);
			}
		}
		
		$this->__call('rmdir', array($remote_dir));
	}
	
	/**
	 * ファイルを削除する
	 * @param string $remote_path
	 */
	public function deleteFile($remote_path) {
		$remote_dir = $this->_dirname($remote_path);
		if (!$this->isDir($remote_dir)) {
			return;
		}
		
		$list = $this->nlist('-a ' . $remote_dir);
		if ($list && in_array($remote_path, $list, true)) {
			$this->delete($remote_path);
		}
	}
	
	/**
	 * ディレクトリがあるかチェックする
	 * @param string $path
	 * @return boolean
	 */
	public function isDir($path) {
		$current = $this->pwd();
		$ret = false;
		try {
			$this->chdir($path);
			$ret = true;
		}
		catch (CustomException $e) {}
		
		$this->chdir($current);
		return $ret;
	}
	
	protected function _dirname($path) {
		return str_replace('\\', '/', dirname($path));
	}

    /**
     * ディレクトリ配下のものを全部消す
     *
     * @param $path
     */
	public function deleteFolderBelow($path) {

        $pre_dir = $this->pwd();

		//HTMLが置かれているディレクトリに移動
		$this->chdir($path);

		$list = $this->rawlist("./");
		foreach($list as $key => $val) {
			$child = preg_split("/\s+/", $val);
			if($child[8] == "." || $child[8] == "..") continue;
			if($child[0][0] === "d") {
				$this->rmdir($child[8]);
			}else{
				$this->delete($child[8]);
			}
		}
		$this->chdir($pre_dir);
	}
}