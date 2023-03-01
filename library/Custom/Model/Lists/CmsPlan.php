<?php
namespace Library\Custom\Model\Lists;

use Library\Custom\Model\Lists\ListAbstract;
use App\Repositories\HpPage\HpPageRepository;

class CmsPlan extends ListAbstract {
    
    protected $_list = array();

    public function __construct() {
        $this->_list = array(
            config('constants.cms_plan.CMS_PLAN_NONE')			=> '（未選択）'		,
            config('constants.cms_plan.CMS_PLAN_ADVANCE')		=> 'アドバンス'		,
            config('constants.cms_plan.CMS_PLAN_STANDARD')		=> 'スタンダード'	,
            config('constants.cms_plan.CMS_PLAN_LITE')          => 'ライト'       ,
        );
    }
    
    public function getCmsPLanNameByList( $cmsPlanVal )
    {
        return $this->_list[ $cmsPlanVal ] ;
    }

    static public function getCmsPLanName( $cmsPlanVal )
    {   
        $plan	= "unknown"		;
        switch ( $cmsPlanVal ) {
            case config('constants.cms_plan.CMS_PLAN_ADVANCE')		:
                $plan	= "advance"			;
                break	;
            case config('constants.cms_plan.CMS_PLAN_STANDARD')	    :
                $plan	= "standard"		;
                break	;
            case config('constants.cms_plan.CMS_PLAN_LITE')         :
                $plan   = "lite"            ;
                break   ;
        }
        
        return $plan ;
    }

    static public function getValuePlan( $class_name )
    {
        $plan   = 0     ;
        switch ( $class_name ) {
            case 'Library\Custom\Plan\Advance':
                $plan   = config('constants.cms_plan.CMS_PLAN_ADVANCE')         ;
                break   ;
            case 'Library\Custom\Plan\Standard':
                $plan   = config('constants.cms_plan.CMS_PLAN_STANDARD')        ;
                break   ;
            case 'Library\Custom\Plan\Lite':
                $plan   = config('constants.cms_plan.CMS_PLAN_LITE')            ;
                break   ;
        }
        
        return $plan ;
    }
    
    static public function getBlockMenuFixedPlanLite()
    {
        return [
            HpPageRepository::TYPE_FORM_LIVINGLEASE,
            HpPageRepository::TYPE_FORM_OFFICELEASE,
            HpPageRepository::TYPE_FORM_LIVINGBUY,
            HpPageRepository::TYPE_FORM_OFFICEBUY,
        ];
    }

    static public function getBlockMenuFreePlanLite()
    {
        return [
            HpPageRepository::TYPE_ESTATE_ALIAS,
            HpPageRepository::TYPE_LINK_HOUSE,
        ];
    }

    static public function getBlockSiteMapPlanLite()
    {
        return array_merge( self::getBlockMenuFixedPlanLite(), self::getBlockMenuFreePlanLite() );
    }
}