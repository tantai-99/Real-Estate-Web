<?php
namespace App\Repositories\MTheme;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Traits\MySoftDeletes;
use Library\Custom\Model\Lists;

use function Symfony\Component\Translation\t;

class MThemeRepository extends BaseRepository implements MThemeRepositoryInterface
{
    use MySoftDeletes;

    protected $_name = 'm_theme';
    protected $_planColumn		;
    
    const CUSTOM_THEME = 1;   //カスタムテーマ
    const NORMAL_THEME = 0;   //標準テーマ

    static $themeName = array();

    public function getModel()
    {
        return \App\Models\MTheme::class;
    }

    public function __construct()
    {
        parent::__construct();
        $cmsPlan			= config('constants.cms_plan.CMS_PLAN_ADVANCE')				;
        $profile			= getInstanceUser('cms')->getProfile()			;
        if ( $profile && isset( $profile[ 'cms_plan' ] ) )
        {
            $cmsPlan			= $profile->cms_plan								;
        }
        $this->setPlan( $cmsPlan )	;
    }
    
    public	function setPlan( $planVal )
    {
        $planName			= Lists\CmsPlan::getCmsPLanName( $planVal )	;
        $this->_planColumn	= "plan_{$planName}"									;
    }
    
    /**
     * 標準テーマを取得する
     */
    public function getNomalData() {
        $select = $this->model->select("*",$this->_planColumn.' AS plan');
        // $select->selectRaw($this->_planColumn.' AS plan');
        // $select->from( array( "mt" => $this->_name ), array( "*",  "plan" => $this->_planColumn ) ) ;
        $select->where('custom_flg', self::NORMAL_THEME);
        $select->orderBy("m_theme.view_sort");
        $select->orderBy("m_theme.id");
        return $select->get();
    }

    /**
     * 顧客別にカスタム用のテーマを取得する 
     */
    public function getCustomData($company_id) {
        $select = DB::table($this->_name.' AS mt');
        $select->leftJoin('custom_theme_company AS cm', function($join) {
            $join->on('mt.id', 'cm.theme_id');
        });
        $select->where('mt.custom_flg', self::CUSTOM_THEME);
        $select->where("cm.company_id", $company_id);
        $select->where("mt.delete_flg", 0);
        $select->where("cm.delete_flg", 0);
        $select->orderBy("mt.view_sort");
        $select->orderBy("mt.id");
        return $select->get();
    }

    public function fetchAllTheme() {
        $select			= $this->model->select( "*","$this->_planColumn as plan")	;
        // $select->from( $this->_name, array( "*",  "plan" => $this->_planColumn ) ) 	;
        $select->orderBy("m_theme.view_sort");
        $select->orderBy("m_theme.id");
        return $select->get()										;
    }
    
    public function fetchTheme( $id ) {
        $select = $this->model->select("*","$this->_planColumn AS plan");
        $select->where('id', $id);
        $select->orderBy("m_theme.view_sort");
        $select->orderBy("m_theme.id");
        return $select->first();
        // return $this->find($id);
    }




    static function isFreeColorTheme($id){

        if(!$id || $id == ""){
            return false;
        }

        $freeColorTheme = array("standard02_custom_color","natural02_custom_color","simple02_custom_color");
        $themeName = self::getThemeName($id);
        if(in_array($themeName, $freeColorTheme)){
            return true;
        }
        return false;
    }


    static function getThemeName($id){

        if(!$id || $id == ""){
            return "";
        }
        if (!array_key_exists($id,self::$themeName)) {
            $theme = new MThemeRepository();
            $row = $theme->fetchTheme( $id );
            self::$themeName[$id] = $row->name;
        }
        return self::$themeName[$id];
    }
}
