<?php
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use Library\Custom\Model\Estate;
$pageTypeCode = $view->page->getRow()["page_type_code"];
$option = '';
$options = [];
$hpRow = \App::make(HpRepositoryInterface::class)->find($view->page->getHpId());
$setting = $hpRow->getEstateSetting();

if (!empty($view->page->getRow()["id"])) {
  $hpMainPart = \App::make(HpMainPartsRepositoryInterface::class)->fetchRow([
    ['parts_type_code', HpMainPartsRepository::PARTS_FREEWORD],
    ['hp_id', $view->page->getHpId()],
    ['page_id', $view->page->getRow()["id"]]
  ]);
}
if(!empty($hpMainPart)) {
    $sortMap=[
        1 => $hpMainPart->attr_1,
        2 => $hpMainPart->attr_2,
        3 => $hpMainPart->attr_3,
        4 => $hpMainPart->attr_4,
    ];
} else {
    $sortMap=[
        1 => null,
        2 => null,
        3 => null,
        4 => null,
    ];
}

if (getActionName() === 'previewPage') {
    $sortMap=[
        1 => null,
        2 => null,
        3 => null,
        4 => null,
    ];
    if (isset(app('request')->main)) {
        foreach(app('request')->main as $mainPart) {
            if(isset($mainPart['parts']) && $mainPart['parts'][0]['parts_type_code'] == HpMainPartsRepository::PARTS_FREEWORD) {
                $freewordParts = $mainPart['parts'][0];
                // 居住用賃貸:living_lease
                $colName = 'living_lease';
                if(isset($freewordParts[$colName]) && !empty($freewordParts[$colName]) && is_numeric($freewordParts[$colName])) {
                    $sortMap[1] = $freewordParts[$colName];
                }
                // 事業用賃貸:office_lease
                $colName = 'office_lease';
                if(isset($freewordParts[$colName]) && !empty($freewordParts[$colName]) && is_numeric($freewordParts[$colName])) {
                    $sortMap[2] = $freewordParts[$colName];
                }
                // 居住用売買:living_buy
                $colName = 'living_buy';
                if(isset($freewordParts[$colName]) && !empty($freewordParts[$colName]) && is_numeric($freewordParts[$colName])) {
                    $sortMap[3] = $freewordParts[$colName];
                }
                // 事業用売買:office_buy
                $colName = 'office_buy';
                if(isset($freewordParts[$colName]) && !empty($freewordParts[$colName]) && is_numeric($freewordParts[$colName])) {
                    $sortMap[4] = $freewordParts[$colName];
                }
                break;
            }
        }
    }
}

if ($setting) {
    $classes = Estate\ClassList::getInstance()->getAll();
    foreach ($classes as $class=>$tilteClass) {
            $searchSetting = $setting->getSearchSetting($class);
            if ($searchSetting) {
                $type = explode(',', $searchSetting['enabled_estate_type']);
                if(count($type) == 1) {
                    $valueSearch = $type[0];
                } else {
                    $valueSearch = Estate\TypeList::getInstance()->getCompositeType($type);
                }
                $typeSearch = Estate\TypeList::getInstance()->getUrl($valueSearch);
                if (isset($searchSetting) && $searchSetting['display_freeword'] == 1 && !empty($sortMap[$class])) {
                    $options[$sortMap[$class]] = '<option value="'.$typeSearch.'">'.$tilteClass.'</option>';
                }
            }
    }
    ksort($options);
    foreach($options as $opt){
        $option .= $opt;
    }

    $inputPlaceholder = '種別を選択してください';

    if(count($options) === 1){
        $optDom = str_get_html($option);
        $results = $optDom->find("option");
		$optVal = '';
		$optTxt = strip_tags($option);
		foreach($results as $res) {
			$optVal = $res->getAttribute('value');
        }
		$option = '<input type="hidden" class="search-type mainparts-search-type" value="'.$optVal.'">';
		$option.= '<p>'.$optTxt.'</p>';
		switch($optVal) {
            case 'chintai':
				$inputPlaceholder = '例：12.2万円以下 和室';
				break;

            case 'kasi-tenpo':
            case 'kasi-office':
            case 'parking':
            case 'kasi-tochi':
            case 'kasi-other':
            case 'chintai-jigyo-1':
            case 'chintai-jigyo-2':
            case 'chintai-jigyo-3':
				$inputPlaceholder = '例：12.2万円以下 駐車場あり';
				break;

            case 'mansion':
            case 'kodate':
            case 'uri-tochi':
            case 'baibai-kyoju-1':
            case 'baibai-kyoju-2':
            case 'baibai-kyoju-3':
				$inputPlaceholder = '例：2000万円以下 南向き';
				break;

            case 'uri-tenpo':
            case 'uri-office':
            case 'uri-other':
            case 'baibai-jigyo-1':
            case 'baibai-jigyo-2':
				$inputPlaceholder = '例：2000万円以下 駐車場あり';
				break;

            default :
				break;
		}
    }else {
        $option = '<option value="all">選択してください</option>'.$option;        
    }
}
?>
<?php if ($option !== '') : ?>
<section>
    <?php if (getActionName() !== 'previewPage') : ?>
    <form class="form-search-freeword">
    <?php endif; ?>
    <h2 class="heading-lv1"><span>フリーワード検索</span></h2>
    <div class="element element-freeword">
        <ul class="inline">
            <li class="fulltext_type">
                <?php if(count($options) === 1): ?>
                    <?php echo $option; ?>
                <?php else : ?>
                    <select class="search-type mainparts-search-type">
                        <?php echo $option; ?>
                    </select>
                <?php endif ?>
            </li>
            <li class="fulltext_input">
                <datalist class="suggesteds" id="suggesteds_main"></datalist>
                <input placeholder="<?php echo $inputPlaceholder;?>" class="" autocomplete="off" name="search_filter[fulltext_fields]" type="text" value="" list="suggesteds_main" id="freeword-mainparts-suggested">
            </li>
            <li class="fulltext_count_label"><label>該当件数</label></li>
            <li class="fulltext_count fulltext_count_mainparts">
                <i>0</i>
                <i>0</i>
                <i>0</i>
                <i>0</i>
                <i>0</i>
            </li>
            <li class="ml10">件</li>
            <li><button class="btn-search-top mainparts-btn-search-top">検索</button></li>
        </ul>
    </div>
    <?php if (getActionName() !== 'previewPage') : ?>
    </form>
    <?php endif; ?>
</section>
<?php endif; ?>
