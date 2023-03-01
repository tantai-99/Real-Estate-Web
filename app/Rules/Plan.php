<?php
namespace App\Rules;

class Plan extends CustomRule
{
	const	INVALID		= 'invalid'		;
	
	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected	$_messageTemplates	= array(
			self::INVALID => "プランを選択して下さい。"
	);
	
	protected	$_params				;
	
	/**
	 * コンストラクタ
	 */
	public function __construct( &$params )
	{
		$this->_params		=& $params		;
	}
	
	/**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if ( $this->_params[ 'basic' ][ 'id' ] != "" )
		{	// 編集なら
			if (	// ATHOME_HP_DEV-2608 【契約登録、プラン変更、オプション追加】契約情報予約のプランがプルダウンで表示されている
				$this->_params['reserve'][ 'reserve_applied_start_date'	] == ""	&&
				$this->_params['reserve'][ 'reserve_start_date'			] == ""	&&
				$this->_params['reserve'][ 'reserve_contract_staff_id'	] == ""
			) {
				return true ;
			}
		}
		
		if ( $this->_params[ 'basic' ][ 'contract_type' ] == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE') )
		{
			return true ;		// 「評価・分析のみ契約」ならチェックしない
		}
		
		if ( $value == config('constants.cms_plan.CMS_PLAN_NONE' ))
		{
			$this->invokableRuleError($fail,  self::INVALID ) ;
			return false ;
		}
		
		return true ;
    }
}