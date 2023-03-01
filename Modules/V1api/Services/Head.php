<?php
namespace Modules\V1api\Services;

class Head
{
	public $title;
	public $keywords;
	public $description;
	
	public function html() {
		$result = '';
		if (!empty($this->title)) {
			$result .= "<title>" . htmlspecialchars($this->title) ."</title>\n";
		}
		if (!empty($this->keywords)) {
			$result .= "<meta name='keywords' content='" . htmlspecialchars($this->keywords). 	"'/>\n";
		}
		if (!empty($this->description)) {
			$result .= "<meta name='description' content='" . htmlspecialchars($this->description). "'/>\n";
		}
        return $result;
    }
}