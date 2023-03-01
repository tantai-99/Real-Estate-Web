<?php
use Library\Custom\Publish\Render\AbstractRender;
use Library\Custom\Hp\Page\Parts\EstateKoma;

$html= <<< 'EOD'
<?php
require_once(APPLICATION_PATH.'/../script/KomaTop.php');
$params = array(
	'media'=>'sp',
    'special-path'  => '{specialPath}',
    'rows'          => {rows},
    'columns'       => {columns},
    'sort-option'   => {sort_option}
);
$komatop = new KomaTop();
$dataKoma = json_decode($komatop->run($params));
$themeKoma ='';
if ($dataKoma) {
	$themeKoma = file_get_contents(APPLICATION_PATH.'/common/sp/themeKoma/{theme_koma}');
	if ($themeKoma) {
        echo '<div class="top__bukkenkoma koma__special_{special_id}">';
		foreach ($dataKoma as $index=>$koma) { 
			$item=$themeKoma;
            if ($index == 0) {
                echo '<div class="koma__detail top__bukenkoma_1">';
            }
            if ($index != 0 && $index%{columns} == 0) {
                echo '</div>';
                echo '<div class="koma__detail top__bukenkoma_'.($index/{columns} + 1).'">';
            }
			foreach ($koma as $key=>$value) {
			    if($key == 'realestate_url'){
			        $item = str_replace('{'.$key.'}', '{base_url}'. $value,$item);
			        continue;
			    }
			 	$item = str_replace('{'.$key.'}',$value,$item);
			}
			echo $item;
		}
        echo '</div>';
        echo '</div>';
	}
}
?>
EOD;
$publishType = $view->mode;
$baseUrl = AbstractRender::protocol($publishType).AbstractRender::www($publishType).AbstractRender::prefix($publishType).$view->company->domain;
$html = str_replace('{specialPath}',$view->element->getSpecialPath(),$html);
$html = str_replace('{rows}',$view->element->getValue(EstateKoma::SP_ROWS),$html);
$html = str_replace('{columns}',$view->element->getValue(EstateKoma::SP_COLUMNS),$html);
$html = str_replace('{sort_option}',$view->element->getValue(EstateKoma::SORT_OPTION),$html);
$html = str_replace('{theme_koma}','_special'.$view->element->getValue('special_id').'.html',$html);
$html = str_replace('{special_id}',$view->element->getValue('special_id'),$html);
$html = str_replace('{base_url}',$baseUrl,$html);
// call api;
echo $html;
// render html;

