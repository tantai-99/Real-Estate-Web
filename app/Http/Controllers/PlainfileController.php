<?php
namespace App\Http\Controllers;
/**
 * Plainなファイルを（条件に寄って切り替えたりしながら）返す為のコントローラー
 * 
 * @author kazuya
 *
 */
use Library\Custom\User\UserAbstract;
use Library\Custom\Model\Lists\CmsPlan;

class PlainfileController extends Controller
{
	/**
	 * CKeditorのプラン別Configファイル取得
	 */
    public function ckeditorconfig()
    {
    	$user = UserAbstract::factory(getModuleName());
    	$plan = CmsPlan::getCmsPLanName( $user->getProfile()->cms_plan	) ;
    	$fileName = "config.js";
    	$filePath = public_path("js/libs/ckeditor/configs/{$plan}/{$fileName}");
    	$content = file_get_contents($filePath);
    	$this->_output($content, 'js', $fileName) ;
    }

    /**
     * 「装飾画面素材集」の一括ダウンロード
     */
    public function decorationFiles()
    {
    	$prams		= $this->_request->all()	;
    	$zipFile	= "DecorationFiles.zip"				;
    	if ( isset( $prams[ 'url' ] ) && !isset( $prams[ 'step2' ] ) )
    	{
    		$zip		= new \ZipArchive()					;
    		$zipPath	= "/tmp/{$zipFile}"					;
    		$zip->open( $zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE )	;
    		foreach ( $prams[ 'url' ] as $url )
    		{
    			$fileName	= basename( $url )								;
    			$filePath	= app_path() . "/../public/{$url}"		;
    			$zip->addFile( $filePath, "DecorationFiles/{$fileName}" )	;
    		}
    		$zip->close()	;
    		$this->_output( file_get_contents( $zipPath ), 'zip', $zipFile ) ;
    	}
    	else
    	{
    		$this->_forward( 'decoration', 'utility', null, $prams ) ;
    	}
    }
    
    protected function _contentType( $ext )
    {
        switch ( $ext ) {
            case 'js'	:
                return 'text/javascript'			;
            case 'zip'	:
            	return 'application/zip'			;
            default		:
                break ;
        }
        throw new InvalidArgumentException( 'unknown type' ) ;
    }

    protected function _output( $content, $type, $name )
    {
        header( "Content-Type: {$type}"								) ;
        header( "Content-Length: "			. strlen( $content )	) ;
        header('Content-Disposition: inline; filename="'.$name.'"');
        header( "Cache-Control: public, must-revalidate, max-age=0"	) ;
        header( "Pragma: public"									) ;
        echo $content ;
        exit() ;
    }
}