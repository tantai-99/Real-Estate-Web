<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;
use App\Repositories\HpFileContent\HpFileContentRepositoryInterface;

class FileController extends Controller
{

    public function hpFile(Request $request)
    {
        $hp = getUser()->getCurrentHp();
        if (!$hp) {
            $this->_forward404();
        }

        $id = $request->id;
        if (!$id || !is_numeric($id)) {
            $this->_forward404();
        }
        $table = App::make(HpFileContentRepositoryInterface::class);
        $row = $table->fetchRow([['id', (int)$id]]);
        if (!$row) {
            $this->_forward404();
        }

        $this->_output($row->content, $this->_contentType($row->extension), $row->filename);
    }

    public function hpFile2(Request $request) {
    	$hp = getUser()->getCurrentHp();
    	if ( !$hp ) {
    		$this->_forward404();
    	}
    	
    	$id = $request->id;
    	if ( !$id ) {
    		$file2Id = $request->file2_id;
    		if ( !$file2Id ) {
    			$this->_forward404();
    		}
    		$table = App::make(HpFile2RepositoryInterface::class);
    		$row = $table->fetchRow([['id', (int)$file2Id], ['hp_id', $hp->id]]);
    		if ( !$row ) {
    			$this->_forward404();
    		}
    		$title = $row->title;
    		
    		$id = $row->hp_file2_content_id ;
    	}
        $table = App::make(HpFile2ContentRepositoryInterface::class);
    	$row = $table->fetchRow([['id', $id], ['hp_id', $hp->id]]);
    	if ( !$row ) {
    		$this->_forward404();
    	}
    	
    	$this->_output( $row->content, $this->_contentType( $row->extension ), "{$title}.$row->extension");
    }
    
    protected function _contentType($ext)
    {
        switch ($ext) {
            case 'pdf':
                return 'application/pdf';
            case 'xls':
            case 'xlsx'		:
                return 'application/vnd.ms-excel';
            case 'doc':
            case 'docx'		:
                return 'application/msword';
            case 'ppt':
            case 'pptx':
                return 'application/vnd.ms-powerpoint';
            default:
                break;
        }

        throw new Exception('unknown type');
    }

    protected function _output($content, $type, $name)
    {
        header('Content-Type: ' . $type);
        header('Content-Length: ' . strlen($content));
        header( 'Content-disposition: attachment; filename*=UTF-8\'\'' . rawurlencode( $name ) ) ;
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        echo $content;
        exit();
    }
}