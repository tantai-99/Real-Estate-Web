<?php
namespace Modules\V1api\Models;

/**
 * リクエストパラメータ：eki_ctは「沿線ローマ字−駅ローマ字」で構成される。
 * 「沿線ローマ字−駅ローマ字」文字列を容易に取り扱うためのクラス。
 */
class EnsenEki
{   
	private $ekiCt;
	private $ensenCt;	
	
    private function __construct($ensenCt, $ekiCt)
	{
		$this->ensenCt = $ensenCt;
		$this->ekiCt = $ekiCt;
	}
		
    public function getEnsenCt() {
    	return $this->ensenCt;
    }
		
    public function getEkiCt() {
    	return $this->ekiCt;
    }
		
    public function getEnsenEkiCt() {
    	return $this->ensenCt . '-' . $this->ekiCt;
    }
	
	public static function getObjByPair($ensenCt, $ekiCt)
	{
		if (empty($ensenCt) || empty($ekiCt)) {
			throw new \Exception("Illegal Values. ensen=$ensenCt eki=$ekiCt");
		}
		return new EnsenEki($ensenCt, $ekiCt);
	}
	
	public static function getObjBySingle($ensenEkiCt)
	{
		$value = explode('-', $ensenEkiCt);
		if (!is_array($value) || count($value) < 2) {
			throw new \Exception('Illegal Value. ensenEkiCt=' . $ensenEkiCt . ' cnt='. count($value));
		}
		return new EnsenEki($value[0], $value[1]);
	}
}