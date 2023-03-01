<?php 
namespace Library\Custom\Model\Lists;

class HistoryYear extends ListAbstract {
	
	static protected $_instance;
	
	const START_YEAR = 1900;
	
	protected $_era = array(
			'明治',
			'大正',
			'昭和',
			'平成',
	);
	
	protected $_start_year = array(
			1868,
			1912,
			1926,
			1989,
	);
	
	public function __construct() {
		
		$list = array();
		
		$year = (int)date('Y');
		
		$current = 0;
		for ($i = self::START_YEAR; $i <= $year; $i++) {
			
			while (isset($this->_start_year[$current + 1]) && $i - $this->_start_year[$current + 1] >= 0) {
				$current += 1;
			}
			
			$diff = $i - $this->_start_year[$current];
			if ($diff == 0) {
				$wareki = $this->_era[$current] . '元';
			}
			else {
				$wareki = $this->_era[$current] . ($diff + 1) ;
			}
			
			$wareki .= '年';
			
			$list[$i] = $i . '年（' . $wareki . '）';
		}
		
		$this->_list = $list;
	}
	
}