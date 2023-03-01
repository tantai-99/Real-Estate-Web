<?php
namespace App\Rules;

class File2Content extends CustomRule
{
	const		INVALID = 'invalid'	;
	
	protected	$_table				;
	protected	$_hpId	= 0			;
	protected	$_where	= array()	;
	
	public function __construct( $options = array() )
	{
		if ( $options instanceof Zend_Config )
		{
			$options = $options->toArray() ;
		} else if ( !is_array( $options ) || ( func_num_args() > 1 ) )
		{
			$args		= func_get_args()	;
			$options	= array()			;
			if ( isset( $args[ 0 ] ) ) { $options[ 'table' ] = $args[ 0 ] ; }
			if ( isset( $args[ 1 ] ) ) { $options[ 'hp_id' ] = $args[ 1 ] ; }
			if ( isset( $args[ 2 ] ) ) { $options[ 'where' ] = $args[ 2 ] ; }
		}
		
		if ( isset( $options[ 'table' ] ) ) {
			$this->setTable( $options[ 'table' ] ) ;
		}
		if ( isset( $options[ 'hp_id' ] ) ) {
			$this->setHpId( $options[ 'hp_id' ] ) ;
		}
		if ( isset( $options[ 'where' ] ) ) {
			$this->setWhere( $options[ 'where' ] );
		}
	}
	
	public function setTable( $table ) {
		$this->_table = $table ;
		return $this ;
	}
	
	public function setHpId( $hpId ) {
		$this->_hpId = $hpId ;
		return $this ;
	}
	
	public function setWhere( $where ) {
		$this->_where = $where ;
		return $this ;
	}
	
	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array(
			self::INVALID => "ファイルがみつかりません。もう一度アップロードしてください。"
	);
	
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
		if (isEmpty( $value ) ) {
			return true ;
		}
		$value = (int) $value ;
		$where = array_merge( array(['id', $value], ['hp_id', $this->_hpId]), $this->_where ) ;
		if ( !$this->_table->fetchRow( $where ) ) {
			$this->invokableRuleError($fail,  self::INVALID ) ;
			return false ;
		}
		
		return true;
	}
}