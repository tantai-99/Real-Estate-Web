<?php
namespace App\Http\Controllers;

use Library\Custom\Model\Lists\CmsPlan;
use App\Http\Form;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;

class UtilityController extends Controller {
	use JsonResponse;
	protected	$_cms_plan			;

    public function init($request, $next) {
		$this->_cms_plan	= getInstanceUser('cms')->getProfile()->cms_plan		;
		return $next($request);
    }

    public function index()
    {
		switch ( $this->_cms_plan )
    	{
    	  case config('constants.cms_plan.CMS_PLAN_STANDARD')	:
    		$this->view->decoration		= false				;
    		break ;
    	  case config('constants.cms_plan.CMS_PLAN_ADVANCE')		:
    	  	$this->view->decoration		= true				;
    	  	break ;
          case config('constants.cms_plan.CMS_PLAN_LITE')      :
            $this->view->decoration     = false              ;
            break ;
		}
		
		return view('utility.index');
    }

    public function underConstructionAction() {
    }


    public function mainImageGuideline() {
		return view('utility.main-image-guideline');
    }
	
	public function decorationGuideline()		{
		return view('utility.decoration-guideline');
	}
    
	public function mainImage()
	{
		$prams = app('request')->all();
		$this->view->imgsize = isset( $prams[ 'imgsize' ] ) ? $prams[ 'imgsize' ] : '720';
		$this->view->plan = $this->_cms_plan;
		$planName = CmsPlan::getCmsPLanName( $this->_cms_plan );
		$systemDir = public_path();
		$imageDir = "{$systemDir}/images/utility/main-image/{$planName}";
		$size = ( $this->view->imgsize == 980 ) ? 'w' : '';
		$kind = isset( $prams[ 'kind' ] ) ? $prams[ 'kind' ] : 'interior';
		$this->view->result	= glob( "{$imageDir}/*{$kind}*[0-9]{$size}.jpg");
		$paging = isset( $prams['paging'] ) && !empty( $prams['paging'] ) ? $prams['paging'] : 1;
		$this->view->pagingNo = $paging;
		$this->view->page = ceil( count( $this->view->result ) / 20 );
		$this->view->result	= array_slice( $this->view->result, ( $paging - 1 ) * 20 ,20);
		$this->view->result	= str_replace( $systemDir, '',  $this->view->result	);
		$valtitle = ['interior' => 'インテリア','person' => '人物','landscape'=>'風景','building'=>'建物','traffic'=>'交通','pet'=>'ペット','miniature'=>'ミニチュア','other'=>'その他'];
		$this->view->title = $valtitle[$kind];
		$this->view->kind = $kind;
		return view('utility.main-image');
    }

    public function favicon() {
		return view('utility.favicon');
    }

    public function decorationImage() {
		return view('utility.decoration-image');
    }
    
    public function decoration()
    {
    	$prams	= app('request')->all();
    	$this->view->form = new Form\Decoration( $prams ) ;
        $this->view->prams = $prams	;
		$this->view->files = [];
		return view('utility.decoration');
    }

    public function apiDecoration() {
        $prams	= app('request')->all()	;
		$form = new Form\Decoration( $prams ) ;
		$files	= array() ;
		$data = [];
    	if ( isset( $prams[ 'step2' ] ) )
    	{
    		list( $design, $color ) = explode( ',', $prams[ 'step2' ] )	;
    		foreach ( $prams as $key => $param )
    		{
    			if ( $param == "1" )
    			{
    				$pattern	= $form->getPattern( $key ) ;
    				$files		= array_merge( $files, $this->getDecorationFiles( $design, $color, $pattern ) ) ;
    			}
            }
            $data['files'] = $files;
		}
		return $this->success($data);
    }
	
	private function getDecorationFiles( $design, $color, $pattern )
	{
		$result	= array()								;
		$url	= "/images/decoration/{$design}"		;
		$path	= public_path().$url	;
		$kind	= substr( $pattern, 1, -1 )				;
		$result[]	= "{$url}/320/{$design}{$kind}bt320_{$color}.png"		;
		$result[]	= "{$url}/210/{$design}{$kind}bt210_{$color}.png"		;
		$result[]	= "{$url}/200/{$design}{$kind}bt200_{$color}.png"		;
		return $result	;
	}
	
    private function getDecorationFilesByPattern( $design, $color, $pattern )		// 使わない事が確定したら消す
    {
    	$result	= array()								;
    	$url	= "/images/decoration/{$design}"		;
    	$path	= APPLICATION_PATH . "/../public{$url}"	;
    	$dirs	= scandir( $path )						;
    	array_shift( $dirs )							;		// .
    	array_shift( $dirs )							;		// ..
    	foreach ( $dirs as $dir )
    	{
    		$path2	= "{$path}/$dir"		; 
    		$files	= scandir( $path2 )		;
    		foreach ( preg_grep( "/{$color}/", preg_grep( $pattern, $files ) ) as $file )
    		{
    			$result[]	= "{$url}/{$dir}/{$file}"		;
    		}
    	}
    	return $result	;
    }
    
	public function illustration() 
	{
		switch ( $this->_cms_plan )
    	{
    	  case config('constants.cms_plan.CMS_PLAN_STANDARD'):
    		$this->view->is_adv_std	= true;
    		break;
    	  case config('constants.cms_plan.CMS_PLAN_ADVANCE'):
    	  	$this->view->is_adv_std	= true;
    	  	break;
          case config('constants.cms_plan.CMS_PLAN_LITE'):
            $this->view->is_adv_std = false;
            break;
		}
		$prams = app('request')->all();
		$systemDir = public_path();
		$imageDir = "{$systemDir}/images/utility/illustration/";
		if ($this->view->is_adv_std){
			$kind = isset( $prams['kind'] ) ? $prams['kind'] : 'person_a';
		}else{
			$kind = isset( $prams['kind'] ) ? $prams['kind'] : 'others';
		}
		$this->view->result	= glob("{$imageDir}{$kind}/*.*");
		$paging = isset( $prams['paging'] ) && !empty( $prams['paging'] ) ? $prams['paging'] : 1;
		$this->view->pagingNo = $paging;
		$this->view->page = ceil( count( $this->view->result ) / 20 );
		$this->view->result	= array_slice( $this->view->result, ( $paging - 1 ) * 20 ,20);
		$this->view->result	= str_replace( $systemDir, '',  $this->view->result	);
		$valtitle = ['person_a' => '人物A','person_b' => '人物B','person_c'=>'人物C','pet'=>'ペット','building_a'=>'建物（平面）','building_b'=>'建物（立体1）','building_c'=>'建物（立体2）','building_d'=>'建物（室内）','disaster' => '災害','others'=>'その他'];
		$this->view->title = $valtitle[$kind];
		$this->view->kind = $kind;
		return view('utility.illustration');
    }

    public function illustrationGuideline() {
		return view('utility.illustration-guideline');
    }

    public function banner() {
		return view('utility.banner');
    }

    public function customer() {
		return view('utility.customer');
	}
	
	public function athomeBanner() {
		return view('utility.athome-banner');
	}

	public function smartApplicationGuideline() {
		return view('utility.smart-application-guideline');
	}

	public function smartApplication() {
		return view('utility.smart-application');
	}

    public function seo(Request $request) {

        $validPdfs=[
			'seo_blog.pdf',
			'seo_campaign.pdf',
			'seo_backnumber.pdf',
			'seo_newpage.pdf',
			'seo_update.pdf',
			'seo_originalpage.pdf',
			'seo_pagevolume.pdf',
			'seo_othersites.pdf',
			'seo_pageadd.pdf',
			'seo_buttonlink.pdf',
			'seo_searchengine.pdf',
			'seo_originaltext.pdf',
			'seo_cleanup.pdf',
			'seo_category.pdf',
			'seo_pagelink.pdf',
			'seo_sitename.pdf',
        ];

        $url = explode('/', $request->getRequestUri());

        $pdf = urldecode($url[count($url)-1]);

        if($pdf==""){
            return view('utility.seo');
        }else{
            if(in_array($pdf, $validPdfs)){
                $file = storage_path('data/utility/seo/pdf/'.$pdf);
                header("Content-Length: " . filesize ( $file ) );
                header("Content-Type: application/pdf");
 //             header("Content-Disposition:inline;filename=SEOお悩み解決.pdf");
                readfile($file);
                exit;
            }
        }

        $this->_forward404();
    }


    public function manual() {
		$file = storage_path('data/utility/manual/hpadvance_manual.pdf');
		header("Content-Length: " . filesize ( $file ) );
		header("Content-Type: application/pdf");
        header("Content-Disposition:inline;filename=hpadvance_manual.pdf");
		readfile($file);
    	exit;
    }

    public function usepoint() {
		$file = storage_path('data/utility/usepoint/hpadvance_usepoint.pdf');
		header("Content-Length: " . filesize ( $file ) );
		header("Content-Type: application/pdf");
        header("Content-Disposition:inline;filename=hpadvance_usepoint.pdf");
		readfile($file);
    	exit;
    }

    public function guidelineAction() {
		$file = storage_path('data/utility/guideline/hpadvance_guideline.pdf');
		header("Content-Length: " . filesize ( $file ) );
		header("Content-Type: application/pdf");
        header("Content-Disposition:inline;filename=hpadvance_guideline.pdf");
		readfile($file);
    	exit;
    }

    public function manualToppageoriginal() {
        $file = storage_path('data/utility/manual/hpadvance_manual_toppageoriginal.pdf');
        header("Content-Length: " . filesize ( $file ) );
        header("Content-Type: application/pdf");
        header("Content-Disposition:inline;filename=hpadvance_manual_toppageoriginal.pdf");
        readfile($file);
        exit;
    }

}