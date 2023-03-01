<?php
/**
 * Themeのバリデーション
 */
namespace App\Rules;

use App;
use App\Repositories\MTheme\MThemeRepositoryInterface;

class Theme extends CustomRule
{
    const INVALID	= 'Invalid'		;

    protected 	$_company_id		;

    public function __construct( $company_id )
    {
    	$this->_company_id	= $company_id	;
    }
    
    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID	=> '現在のプランでは、ご指定のテーマは、ご使用になれません。'
    ) ;

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
        $result	= true	;
    	
    	$table	= App::make(MThemeRepositoryInterface::class)	;
    	$row	= $table->fetchTheme( $value )		;
    	if ( $row->plan == 0 )
    	{
    		$this->invokableRuleError($fail,  self::INVALID )	;
    		$result	= false	;
    	}
    	
    	return	$result	;
    }
}
?>