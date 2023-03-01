<?php
namespace Library\Custom\Estate\Setting;

class SettingAbstract {
	
	public function toSaveData() {
		$data = [];
		foreach (call_user_func('get_object_vars', $this) as $prop => $value) {
			if(in_array($prop, $this->notIncludes())) {
				continue;
			}
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			else if (is_object($value)) {
				$value = json_encode($value);
			}
			$data[$prop] = $value;
		}
		return $data;
	}

	public function notIncludes(){
		return [];
	}
}