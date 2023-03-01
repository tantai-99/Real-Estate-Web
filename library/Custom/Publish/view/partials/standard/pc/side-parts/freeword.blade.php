<?php
use App\Repositories\HpSideParts\HpSidePartsRepository;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use Library\Custom\Model\Estate;
$pageTypeCode = $view->page->getRow()["page_type_code"];
$option = '';
$options = [];
$hpRow = \App::make(HpRepositoryInterface::class)->find($view->page->getHpId());
$setting = $hpRow->getEstateSetting();

if (!empty($view->page->getRow()["id"])) {
  $hpSidePart = \App::make(HpSidePartsRepositoryInterface::class)->fetchRow([
    ['parts_type_code', HpSidePartsRepository::PARTS_FREEWORD],
    ['hp_id', $view->page->getHpId()],
    ['page_id', $view->page->getRow()["id"]]
  ]);
}
if(!empty($hpSidePart)) {
    $sortMap=[
        1 => $hpSidePart->attr_1,
        2 => $hpSidePart->attr_2,
        3 => $hpSidePart->attr_3,
        4 => $hpSidePart->attr_4,
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
    // 編集中の page_idの場合のみ上書き(TOPページサイドの場合あり)
    if(app('request')->id == $view->page->getRow()["id"]) {
        $sortMap=[
            1 => null,
            2 => null,
            3 => null,
            4 => null,
        ];
        if (isset(app('request')->side)) {
            foreach(app('request')->side as $sidePart) {
                if (!is_array($sidePart)) continue;
                if($sidePart['parts_type_code'] == HpSidePartsRepository::PARTS_FREEWORD) {
                    $freewordParts = $sidePart;
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
		$option = '<input type="hidden" class="search-type sideparts-search-type" value="'.$optVal.'">';
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
<div class="side-others-freeword">
    <?php if (getActionName() !== 'previewPage') : ?>
    <form class="form-search-freeword">
    <?php endif; ?>
    <section>
		<h3 class="side-others-heading">フリーワード検索</h3>
		<div>
            <?php if(count($options) === 1): ?>
                <?php echo $option; ?>
            <?php else : ?>
                <select class="search-type sideparts-search-type">
                    <?php echo $option; ?>
                </select>
            <?php endif ?>
		</div>
		<div>
            <datalist class="suggesteds" id="suggesteds_side"></datalist>
            <input placeholder="<?php echo $inputPlaceholder;?>" class="" autocomplete="off" name="search_filter[fulltext_fields]" type="text" value="" list="suggesteds_side" id="freeword-sideparts-suggested">
		</div>
        <div>
            <ul class="inline">
                <li class="fulltext_count fulltext_count_sideparts">
                    <i>0</i>
                    <i>0</i>
                    <i>0</i>
                    <i>0</i>
                    <i>0</i>
                </li>
                <li class="ml10">件</li>
                <li><button class="btn-search-top sideparts-btn-search-top">検索</button></li>
            </ul>
        </div>
    </section>
    <?php if (getActionName() !== 'previewPage') : ?>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>