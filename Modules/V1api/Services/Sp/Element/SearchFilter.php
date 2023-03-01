<?php
namespace Modules\V1api\Services\Sp\Element;
use Library\Custom\Model\Estate;
use Library\Custom\Estate\Setting\searchFilter\SearchFilterAbstract;
use Modules\V1api\Services\BApi;
class SearchFilter {

    const SEARCHCATEGORY_DESIRED = 'desired';
    const SEARCHCATEGORY_PARTICULAR = 'particular';

    protected $_idSuffix = 0;

    /**
     * @var SearchFilterAbstract
     */
    protected $_searchFilter;
    protected $_searchFilterSplCms = null;

    /**
     * @param SearchFilterAbstract $searchFilter
     */
    public function __construct(SearchFilterAbstract $searchFilter) {
        $this->_searchFilter = $searchFilter;
    }

    /** CMSの特集設定の検索条件をセットする
     * 　＃下記カテゴリの条件で特殊な対応が必要なため
     * @param SearchFilterAbstract $searchFilterSplCms
     */
    public function setSearchFilterSplCms($searchFilterSplCms) {
        $this->_searchFilterSplCms = $searchFilterSplCms;
    }

    protected function getJoinedParticularType() {
        return [
            Estate\TypeList::TYPE_PARKING,
            Estate\TypeList::TYPE_KASI_TOCHI,
        ];
    }

    protected function isJoinedParticularType($type_id) {
        return in_array($type_id, $this->getJoinedParticularType());
    }

    public function renderAside($type_id, $total, $facet, $doc) {
        $tmp = pq('<div/>');
        // 希望条件
        $tmp->append( $this->createDesiredAsideElement($facet) );
        // こだわり条件
        $tmp->append( $this->createParticularAsideElement($type_id, $facet) );

        // アサイド検索条件
        $asidetElem = $doc['div.articlelist-side.contents-left'];
        $searchFilterSection = $asidetElem['.articlelist-side-section:last'];
        $searchFilterSection->find('section')->remove();
        $searchFilterSection->find('h3.articlelist-side-heading')->after( $tmp->children() );

        // こだわりモーダル
        if (!$this->isJoinedParticularType($type_id)) {
            $searchFilterSection->find('.contents-iframe.search-modal-detail .total-count')->text(number_format($total)."件");
            $this->_searchFilter->loadEnables($type_id);
            $searchFilterSection->find('.contents-iframe.search-modal-detail .num-and-btn:first')->after( $this->createParticularTableElement($facet) );
        }
        else {
            $asidetElem['.link-more-term']->remove();
        }
    }

    /**
     * 希望条件を作成する
     */
    public function createDesiredTableElement($facet = null) {
        $categories = $this->_searchFilter->pickDesiredCategories();
        $searchCategory = self::SEARCHCATEGORY_DESIRED;
        $div = pq('<div/>');
        foreach ($categories as $category) {

            if($this->_searchFilter->isParsed() && $category->category_id == 'shumoku') {
                $shumokuSearchFilter = null;
                foreach ($this->_searchFilterSplCms->categories as $splCategory) {
                    if($splCategory->category_id == 'shumoku') {
                        $shumokuSearchFilter = $splCategory->items;
                        break;
                    }
                }
                if(!is_null($shumokuSearchFilter)) {
                    $seachShumokuIds = [];
                    foreach($shumokuSearchFilter as $sItem) {
                        $seachShumokuIds[] = $sItem->item_id;
                    }
                    foreach($category->items as $shumokuCategoryItem) {
                        if($shumokuCategoryItem->getParsedValue() == 1) {
                            continue;
                        }
                        $aliasShumokus = BApi\SearchFilterTranslator::getShumokuAliasMapStatic($shumokuCategoryItem->item_id);
                        if(is_null($aliasShumokus)) {
                            continue;
                        }
                        foreach($aliasShumokus as $aliaShumokuId) {
                            if(in_array($aliaShumokuId, $seachShumokuIds)) {
                                $keepValue = $shumokuCategoryItem->item_value;
                                $shumokuCategoryItem->parse(1);
                                $shumokuCategoryItem->item_value = $keepValue;
                            }
                        }
                    }
                }
            }

            if (!$this->_isDispAsideCategory($category)){
                continue;
            }
            if ($category->category_id == 'menseki') {
                foreach ($category->items as $item) {

                    if($item->item_id == 3 || $item->item_id == 4) continue;

                    $section = pq('<section><h4></h4></section>');
                    $section['h4']->text( $item->item_id == 1 ? $category->getLabel() : $item->getLabel() );
                    $tmp = pq('<div/>')->addClass('select-one');
                    $tmp->append($this->_renderListItem($category, $item, $searchCategory, $facet));
                    $section->append($tmp);
                    $div->append($section);
                }
                continue;
            }

            $section = pq('<section><h4></h4></section>');
            $section['h4']->text( $category->getLabel() );
            // （?）アイコン
            if ($category->category_id == 'keiyaku_joken') {
                $section['h4']->append($this->_renderToolTip('agreement'));
            }
            else if ($category->category_id == 'tatemono_kozo') {
                $section['h4']->append($this->_renderToolTip('architecture'));
            }

            $body = pq('<div/>');
            $this->_renderItems($body, $category, $searchCategory, false, $facet);
            if (!$body->children()->length) {
                continue;
            }
            $section->append($body->children());
            $div->append($section);
        }
        $div->find('h4')->addClass('heading-select-set');
        $div->append($this->_renderToolTipFloatbox());
        return $div->children();
    }

    /**
     * こだわり条件を作成する
     */
    public function createParticularTableElement($facet = null) {
        $categories = $this->_searchFilter->pickParticularCategories();
        $searchCategory = self::SEARCHCATEGORY_PARTICULAR;
        $section = pq('<section/>');
        $section->append('<h4 class="heading-select-set">こだわり条件</h4>');
        $section->append('<dl class="element-search-toggle js-search-toggle"></dl>');
        foreach ($categories as $category) {
            $line = pq('<div><dt></dt><dd></dd></div>');
            $line['dt']->text( $category->getLabel() );
            $this->_renderItems($line['dd'], $category, $searchCategory, false, $facet);
            if (!$line['dd']->children()->length) {
                continue;
            }
            $section['dl']->append($line->children());
        }
        return $section;
    }

    /**
     * 希望条件を作成する[アサイド]
     * @param Modules\V1api\Services\BApi\Facet $facet
     */
    public function createDesiredAsideElement($facet = null) {
        return $this->_createAsideElement( $this->_searchFilter->pickDesiredCategories(), self::SEARCHCATEGORY_DESIRED, $facet );
    }

    /**
     *
     * @param Modules\V1api\Services\BApi\SearchFilterFacetTranslator $facet
     */
    public function createFacetJson($facet) {
        $result = [];
        foreach ($this->_searchFilter->pickParticularCategories() as $category) {
            foreach ($category->items as $item) {
                $_facet = $facet->getFacet($category->category_id, $item->item_id);
                switch ($item->getType()) {
                    case 'list':
                        break;
                    case 'multi':
                    case 'radio':
                        $options = $item->getOptions();
                        foreach ($options as $value => $label) {
                            if (!$value) {
                                continue;
                            }
                            $result["{$category->category_id}-{$item->item_id}-{$value}"] =
                                $_facet && isset($_facet[$label]) ? $_facet[$label]: 0;
                        }
                        break;
                    case 'flag':
                        $result["{$category->category_id}-{$item->item_id}"] = $_facet ? $_facet : 0;
                        break;
                    default:
                        break;
                }
            }
        }
        return $result;
    }

    //＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞＞
    // アサイドに非表示にするカテゴリかどうか
    private function _isDispAsideCategory($category) {

        //物件種目・間取り・建物構造・画像
        //→CMSの条件設定で1項目のみの単独選択している場合は非表示にする
        if ( $category->category_id == 'shumoku'         ||
             $category->category_id == 'tatemono_kozo'   ||
             $category->category_id == 'image' ) {
             $count = 0;
            foreach ($category->items as $item) {
                if ($item->getParsedValue() == true){
                    $count++;
                }
            }
            if ($count==1) {
                return false;
            }
        }
        //間取り
        //→CMSの条件設定で1項目のみの単独選択している場合は非表示にする
        elseif ($category->category_id == 'madori') {
            $count = 0;
            $item = $category->items[0];
            $count= count($item->getParsedValue());
            if ($count==1) {
                return false;
            }
        }

        //契約条件
        // →定期借家除く,定期借家のみ,短期貸し物件の場合は非表示
        elseif($category->category_id == 'keiyaku_joken') {
            $item = $category->items[0];
            if( $item->getParsedValue() == 10  || // 定期借家除く
                $item->getParsedValue() == 30  || // 定期借家のみ
                $item->getParsedValue() == 40 ) { // 短期貸し物件
                return false;
            }
            return true;
        }
        //面積
        //→CMSの条件設定で最大値を設定している場合は非表示にする
        elseif($category->category_id == 'menseki') {
            $item = $category->items[0];
            $options = ($item->getOptionModel()->getAll());
            $lastOptionKey  = key(array_slice($options, -1, 1, true));
            if ($item->getParsedValue()==$lastOptionKey){
                return false;
            }
        }
        //駅からの徒歩
        //→CMSの条件設定で最小値を設定している場合は非表示にする
        elseif($category->category_id == 'eki_toho_fun') {
            $item = $category->items[0];
            $options = ($item->getOptionModel()->getAll());
            $firstOptionKey  = key(array_slice($options, 1, 1, true));
            if ($item->getParsedValue()==$firstOptionKey){
                return false;
            }
        }
        //情報公開日
        //→CMSの条件設定で最小値(本日公開)を設定している場合は非表示にする
        elseif($category->category_id == 'joho_kokai') {
            $item = $category->items[0];
            if ($item->getParsedValue()==10){ //本日公開
                return false;
            }
        }

        //築年数
        //→新築、築後未入居の場合は非表示
        //→○年以内の最小値の場合は非表示
        elseif($category->category_id == 'chikunensu') {
            $item = $category->items[0];
            $before_label = '';
            foreach ($item->getOptions() as $value => $label) {
                if ($item->getParsedValue() == $value) {
                    if ((mb_strpos($label, '新築')) ||
                      (mb_strpos($label, '築後未入居')) ||
                      (!mb_strpos($before_label, '年以内') && ($label != '指定なし') )){
                        return false;
                    }
                }
                $before_label = $label;
            }
        }
        // アピール・オープンルーム・モデルルーム・最適用途・現地販売会
        //→設定してあれば非表示
        elseif($category->category_id == 'pro_comment'      ||
               $category->category_id == 'open_room'        ||
               $category->category_id == 'open_house'       ||
               $category->category_id == 'saiteki_yoto'     ||
               $category->category_id == 'genchi_hanbaikai')
       {
            $item = $category->items[0];
            if ($item->getParsedValue()==true){
                return false;
            }
        }
        /*  リフォームリノベーション
         *    リフォームリノベーションはCMSの特集設定とフロントの検索条件が異なるため特殊
         * 　　　CMSの特集設定は個別に設定できる
         * 　　　　　・リフォーム
         * 　　　　　・リノベーション
         * 　　　フロントの検索条件はまとまっている
         * 　　　　　・リフォーム・リノベーション
         */
        elseif($category->category_id == 'reform_renovation'){


            // 特集のでなければ表示する
            // 特集の場合だけ、特集の設定内容をしらべる
            //   下記のいずれか設定されている場合は非表示
            //   　・リフォーム
            //   　・リノベーション
            //   　・リフォーム・リノベーション　※後方互換のため
            if(!is_null($this->_searchFilterSplCms)){
                foreach($this->_searchFilterSplCms->categories as $categorySplcms ){

                    // リフォーム・リノベーションカテゴリの設定内容
                    if($categorySplcms->category_id == $category->category_id){

                        //何かしら設定されていれば表示しない
                        foreach($categorySplcms->items as $item ){
                            if ( $item->getParsedValue() ){
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }
    //＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜

    protected function _renderToolTipFloatbox(){
        $content = <<<EOM
<div class="box-overlay" style="display:none;"></div>
<div class="floatbox" style="top:30px;display:none;">
  <div class="inner">
    <p class="floatbox-heading"></p>
    <div class="floatbox-tx">
    </div>
    <p class="btn-modal-close">閉じる</p>
  </div>
</div>
EOM;
        return pq($content);
    }

    protected function _renderToolTip($add_class) {
        return pq(sprintf('<span class="icon_question detail-%s"><img alt="" src="/sp/imgs/icon_question.png"></span>', $add_class));
    }

    /**
     * @param phpQueryObject $container
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param string $searchCategory
     * @param boolean $aside
     * @param Modules\V1api\Services\BApi\Facet $facet
     */
    protected function _renderItems($container, $category, $searchCategory, $aside = false, $facet = null) {
        $items = [];

        // 価格のセレクトボックス
        if ($category->category_id == 'kakaku') {
            $min = $category->getItem(1);
            $max = $category->getItem(2);
            if ($min || $max) {
                $selectContainer = pq('<div/>')->addClass('select-range')->appendTo($container);
                // 下限
                if ($min) {
                    $selectContainer->append($this->_renderListItem($category, $min, $searchCategory, $facet));
                }
                // ～
                $selectContainer->append('<span>〜</span>');
                // 上限
                if ($max) {
                    $selectContainer->append($this->_renderListItem($category, $max, $searchCategory, $facet));
                }
            }

            foreach ($category->items as $item) {
                if ($item->item_id == 1 || $item->item_id == 2) {
                    continue;
                }
                // cmsで設定されている価格オプションは表示だけする
                if (!$item->isLoaded()) {
                    $kakaku_item[] = $item->getLabel();
                } else {
                    $items[] = $item;
                }
            }
        }
        else {
            $items = $category->items;
        }

        $ul = pq('<ul/>')->addClass('list-select-set');
        $ul->appendTo($container);

        if (isset($kakaku_item)) {
            foreach($kakaku_item as $label){
                $tmp = pq('<li><label><span class="checkbox"></span><span class="name"></span></label></li>');
                $tmp['span.name']->append($label);
                $ul->append($tmp);
            }
        }

        foreach ($items as $item) {

            // 築年数-以上、面積&土地面積はフォーム表示しない
            if($category->category_id == 'chikunensu' && $item->item_id == 2) {
                continue;
            }

            // こだわり条件のみCMS特集の条件に設定されている場合は表示しない
            if (!$this->_searchFilter->isDesiredCategory($category->category_id) &&
              $item->getParsedValue() == true) {
                continue;
            }

            if($category->category_id == 'chikunensu' && $item->item_id == 1) {
                $setZeroFlg = false;
                if(count($item->item_value) == 0) {
                    $setZeroFlg = true;
                    $item->item_value[] = '0';

					$parsedValue = $item->getParsedValue();
					if(isset($parsedValue) && !empty($parsedValue)) {
						$item->item_value[] = $parsedValue;
					}
                }
                $itemElem = $this->_renderRadioItem($category, $item, $searchCategory, null);
                if($setZeroFlg = true) {
                    $item->item_value = [];
                }

				if ($itemElem->find('li')->length == 0) {
					continue;
				}
            } else {
                $itemElem = $this->_renderItem($category, $item, $searchCategory, $facet);
            }

            if (!$itemElem) {
                continue;
            }
            if ($itemElem->filter('li')->length) {
                $ul->append( $itemElem );
            }
            else {
                $container->append( $itemElem );
            }
        }
        if (!$ul->children()->length) {
            $ul->remove();
        }
        if (!$this->_searchFilter->isDesiredCategory($category->category_id)) {
            $container['ul']->addClass('three');
        }
    }

    /**
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param Library\Custom\Estate\Setting\SearchFilter\Item\Abstract $item
     * @param string $searchCategory
     * @param Modules\V1api\Services\BApi\Facet $facet
     * @return \phpQueryObject
     */
    protected function _renderItem($category, $item, $searchCategory, $facet = null) {
        switch ($item->getType()) {
            case 'list':
                $tmp = pq('<div/>')->addClass('select-one');
                return $tmp->append($this->_renderListItem($category, $item, $searchCategory, $facet));
            case 'multi':
                return $this->_renderMultiItem($category, $item, $searchCategory, $facet);
            case 'radio':
                return $this->_renderRadioItem($category, $item, $searchCategory, $facet);
            case 'flag':
                return $this->_renderFlagItem($category, $item, $searchCategory, $facet);
            default:
                return null;
        }
    }

    /**
     * select
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param Library\Custom\Estate\Setting\SearchFilter\Item\List $item
     * @param string $searchCategory
     * @param Modules\V1api\Services\BApi\Facet $facet
     * @return \phpQueryObject
     */
    protected function _renderListItem($category, $item, $searchCategory, $facet = null) {
        if ($searchCategory == self::SEARCHCATEGORY_DESIRED && $this->_searchFilter->isParsed()) {
            $options = [];
            // 価格の場合
            // ・fromとtoの範囲内のオプションを表示する
            if ($category->category_id=='kakaku') {
                $tmp = $category->getItem(1);
                $min = $tmp->getParsedValue();
                $tmp = $category->getItem(2);
                $max = $tmp->getParsedValue();
                foreach ($item->getOptions() as $value => $label) {
                    // 価格上限下限はそれぞれ適応する
                    if (($value >= $min) && (($value <= $max) || !$max) ||
                    ($item->item_id == '2' && $value == $max)) {
                        if ($item->item_id == '2' && $max && !$value) {
                            continue;
                        }
                        $options[$value] = $label;
                    }
                }

            // 契約条件の場合
            // ・「定期借家含む」を選択されている場合はすべて表示する
            //      ※「定期借家含む」： ParsedValue=null
            }elseif ($category->category_id=='keiyaku_joken'){
                foreach ($item->getOptions() as $value => $label) {
                    if (!$item->getParsedValue()) {
                        $options[$value] = $label;
                    }
                }

            // 面積の場合
            // ・CMSで条件設定した値より大きいオプションを表示する
            }elseif ($category->category_id=='menseki'){

                if($item->item_id == 1) {
                    $itemMin = $category->getItem(3);
                } else {
                    $itemMin = $category->getItem(4);
                }
                $mensekiMax = $itemMin->getParsedValue();

                foreach ($item->getOptions() as $value => $label) {
                    if ($value >= $item->getParsedValue()) {
                        if(empty($mensekiMax)) {
                            $options[$value] = $label;
                        } else if(is_numeric($mensekiMax) && $value <= $mensekiMax) {
                            $options[$value] = $label;
                        }
                    }
                }

            // 駅からの徒歩
            // ・CMSで条件設定した値より小さいオプションを表示する
            }elseif ($category->category_id=='eki_toho_fun'||
                     $category->category_id=='joho_kokai'||
                     $category->category_id=='chikunensu'  ){
                foreach ($item->getOptions() as $value => $label) {
                    if ( ($value <= $item->getParsedValue() && $value) ||
                        !$item->getParsedValue()) {
                        $options[$value] = $label;
                    }

                    // 新築除くがcmsで設定されている場合
                    if ($label == '新築') {
                        $new_value = $value;
                    }
                    if ( ($item->getParsedValue() == $value) && ($label == '新築を除く')){
                        if (isset($options[$new_value])) {
                            unset($options[$new_value]);
                        }
                    }
                }
            }else{
                foreach ($item->getOptions() as $value => $label) {
                    if (!$value || $value == $item->getParsedValue()) {
                        $options[$value] = $label;
                    }
                }
            }

            $selected = $item->item_value;
            if($this->_searchFilter->isValueEmpty()){
                $selected = $item->getParsedValue();
            }
        }
        else {
            $options = $item->getOptions();
            $selected = $item->item_value;
        }

        if ($searchCategory == self::SEARCHCATEGORY_DESIRED && $this->_searchFilter->isParsed()) {
            $disabled = $item->isLoaded();

            // ＣＭＳで指定なを設定している場合はenableにする
            if ( !$item->getParsedValue() &&
                 ($category->category_id=='kakaku'       ||
                  $category->category_id=='eki_toho_fun' ||
                  $category->category_id=='chikunensu' ||
                  $category->category_id=='menseki'   )){
                $disabled = false;
            }
        }
        else {
            $disabled = !$item->isLoaded();
        }
        $select = pq('<select name="search_filter['.$category->category_id.']['.$item->item_id.']"'.'></select>');
        foreach ($options as $value => $label) {
            if ($label == '新築を除く') {
                continue;
            }
            $option = pq('<option'.($value == $selected?' selected':'').'></option>')->attr('value', $value)->text($label);
            $select->append($option);
        }
        return $select;
    }

    /**
     * ul li input label
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param Library\Custom\Estate\Setting\SearchFilter\Item\Multi $item
     * @param string $searchCategory
     * @param Modules\V1api\Services\BApi\Facet $facet
     * @return \phpQueryObject
     */
    protected function _renderMultiItem($category, $item, $searchCategory, $facet = null) {
        return $this->_renderMultiCheckItem($category, $item, $searchCategory, 'checkbox', $facet);
    }

    /**
     * li
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param Library\Custom\Estate\Setting\SearchFilter\Item\Radio $item
     * @param string $searchCategory
     * @param Modules\V1api\Services\BApi\Facet $facet
     * @return \phpQueryObject
     */
    protected function _renderRadioItem($category, $item, $searchCategory, $facet = null) {
        return $this->_renderMultiCheckItem($category, $item, $searchCategory, 'radio', $facet);
    }

    /**
     * li
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param Library\Custom\Estate\Setting\SearchFilter\Item\Flag $item
     * @param string $searchCategory
     * @param Modules\V1api\Services\BApi\Facet $facet
     * @return \phpQueryObject
     */
    protected function _renderFlagItem($category, $item, $searchCategory, $facet = null) {
        $label = $item->getLabel();
        $facetNum = null;
        if ($facet && is_numeric($facet->getFacet($category->category_id, $item->item_id))) {
            $facetNum = $facet->getFacet($category->category_id, $item->item_id);
        }

        // 特集
        if ($this->_searchFilter->isParsed()) {

            $checked = !!$item->item_value;
            $isLoaded = $item->isLoaded();
            $disabled = $isLoaded;

            //絞込条件
            if ($searchCategory == self::SEARCHCATEGORY_DESIRED) {
                //価格
                if ($category->category_id=='kakaku') {
                    $disabled = !$isLoaded;

                //種目・建物構造
                }elseif ($category->category_id=='shumoku'      ||
                         $category->category_id=='tatemono_kozo'||
                         $category->category_id=='image') {
                    $checked = $item->item_value;
                    /* 特集チェックボックスのデフォルトはチェックなし
                    if($this->_searchFilter->isValueEmpty()){
                        $checked = $item->getParsedValue();
                    }
                    */

                    //CMSの特集で検索条件に設定されてるアイテムがない場合はすべて表示する
                    $count = $this->_getParsedValidCountForCategory($category);
                    if($count==0){
                        $disabled=false;
                    }

                }elseif ($category->category_id == 'reform_renovation'||
                         $category->category_id == 'pro_comment'      ||
                         $category->category_id == 'open_room'        ||
                         $category->category_id == 'open_house'       ||
                         $category->category_id == 'saiteki_yoto'     ||
                         $category->category_id == 'genchi_hanbaikai') {

                    //CMSの特集で検索条件に設定されてるアイテムがない場合はすべて表示する
                    $count = $this->_getParsedValidCountForCategory($category);
                    if($count==0){
                        $disabled=false;
                    }
                }

            //こだわり条件
            }elseif($searchCategory == self::SEARCHCATEGORY_PARTICULAR){

                $disabled=false;

                //ＣＭＳ特集設定で条件設定されているこだわり条件は表示しない
                if($item->getParsedValue()==true){
                    $disabled=true;
                }
            }

            if($disabled){
                if($category->category_id == 'shumoku') {
                    if($item->getParsedValue() == 1) {
                        $disabled = false;
                    } else {
                return;
                    }
                } else {
                    return;
                }
            }

        //通常検索
        }else {
            $checked = !!$item->item_value;
            $disabled = !$item->isLoaded();
        }
        return $this->_renderCheckItem(
                        "{$category->category_id}-{$item->item_id}",
                        "search_filter[{$category->category_id}][{$item->item_id}]",
                        'checkbox',
                        1,
                        $label,
                        $checked,
                        $disabled,
                        $facetNum);
    }

    /**
     * li
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param Library\Custom\Estate\Setting\SearchFilter\Item\Radio $item
     * @param string $searchCategory
     * @param string $type
     * @param Modules\V1api\Services\BApi\Facet $facet
     * @return \phpQueryObject
     */
    protected function _renderMultiCheckItem($category, $item, $searchCategory, $type, $facet = null) {
        $facetMap = null;
        if ($facet) {
            $facetMap = $facet->getFacet($category->category_id, $item->item_id);
        }

        $notDisplayLabels = [];
        // 新築除くがcmsで設定されている場合、新築を弾く準備
        if ($category->category_id=='chikunensu'){
            $notDisplayLabels = $this->_getNotDisplayLabels($category);
            if(!in_array('指定なし', $notDisplayLabels)) {
                $chikunensu_sel_label = '指定なし';
            } else {
                $chikunensu_sel_label = null;
            }
        }

        $ul = pq('<ul/>')->addClass('list-select-set');
        foreach ($item->getOptions() as $value => $label) {

			if(in_array($label, $notDisplayLabels)) continue;

            if ($facet && $value) {
                $facetNum = isset($facetMap[$label])?$facetMap[$label]:0;
            }
            else {
                $facetNum = null;
            }

            // チェックボックス
            if ($type === 'checkbox') {
                if ($searchCategory == self::SEARCHCATEGORY_DESIRED && $this->_searchFilter->isParsed()) {
                    $checked = in_array($value, $item->item_value);
                    /* 特集チェックボックスのデフォルトはチェックなし
                    if($this->_searchFilter->isValueEmpty()){
                        $checked = in_array($value, $item->getParsedValue());
                    }
                    */

                    //間取り
                    if($category->category_id == 'madori') {
                        //CMSの特集で全アイテム選択していない場合は全て表示する
                        $count = $this->_getParsedValidCountForCategory($category);
                        if($count==0){
                            $disabled=false;
                        }else{
                            //CMSで特集条件を設定されているもののみ表示する
                            $disabled = $value && !in_array($value, $item->getParsedValue());
                        }
                        if ($disabled) {
                            continue;
                        }
                    }else{
                        $disabled = $value && !in_array($value, $item->getParsedValue());
                        $addClass = $disabled? 'sp-disable': '';
                    }
                }
                else {
                    $checked = in_array($value, $item->item_value);
                    $disabled = $checked && !$item->isLoaded();
                }
            }
            // ラジオ
            else {
                // 仕様変更（2016年9月）
                if ($value > $item->getParsedValue() &&
                  !empty($item->getParsedValue())) {
                    break;
                }
                if(gettype($item->item_value) == 'array') {
                    $checked = in_array($value, $item->item_value);
                } else {
                    $checked = $item->item_value == $value;
                }
                if($this->_searchFilter->isValueEmpty()){
                    // 仕様変更（2016年9月）

                    if(gettype($item->getParsedValue()) == 'array') {
                        if($category->category_id == 'chikunensu') {
                            if(!is_null($chikunensu_sel_label)) {
                                if($chikunensu_sel_label == $label) {
                                    $checked = true;
                                }
                            } else {
                                $checked = true;
                            }
                        } else {
                            $checked = $item->getParsedValue()[0] == $value;
                        }
                    } else {
                        $checked = $item->getParsedValue() == $value;
                    }
/*
                    $checked = $item->getParsedValue() == $value;
                    // 新築除くがチェックされているときは指定なしがデフォルト
                    if ($category->category_id == 'chikunensu' && !$shinchiku_show_flag && !$value) {
                        $checked = true;
                    }
*/
                }
                if ($searchCategory == self::SEARCHCATEGORY_DESIRED && $this->_searchFilter->isParsed()) {
                    //情報公開日
                    //・CMSで特集条件を設定値以下を表示する
                    if ($category->category_id == 'joho_kokai' ||
                      $category->category_id == 'eki_toho_fun') {
                        $disabled = true;
                        if( $item->getParsedValue() &&
                          (!$value || $value > $item->getParsedValue())) {
                            continue;
                        }
                    } else if ($category->category_id == 'chikunensu') {
                        $disabled = true;
/*
                        if ($item->getParsedValue()) {
                            if(($value > $item->getParsedValue()) ||
                              (!$value && $shinchiku_show_flag) ) {
                                continue;
                            }
                        }
*/
                    } else {
                        $disabled = $value && $value != $item->getParsedValue();
                    }

                } else {
                    $disabled = !$item->isLoaded();
                }
            }
            // 仕様変更（2016年9月）
            $disabled = false;

            $inputName = "search_filter[{$category->category_id}][{$item->item_id}]";
            if($type == 'checkbox') {
                $inputName = $inputName . '[]';
            } else if(gettype($item->item_value) == 'array') {
                $inputName = $inputName . '[]';
            }
            $li = $this->_renderCheckItem(
                            "{$category->category_id}-{$item->item_id}-{$value}",
                            $inputName,
                            $type,
                            $value,
                            $label,
                            $checked,
                            $disabled,
                            $facetNum);
            $ul->append($li);
        }
        return $ul;
    }

    /**
     * 築年数のフォーム非表示Labelの一覧生成
     */
    protected function _getNotDisplayLabels($category) {
        $notDisplayLabels = [];

        $notDisplayLabels[] = '新築を除く';

        $buf1 = [];
        $buf2 = [];

        $item1 = $category->getItem(1);
        $item2 = $category->getItem(2);

        $code2Label = $item1->getOptions();
        foreach($item1->getParsedValue() as $code) {
            if($code != 0) $buf1[] = $code2Label[$code];
        }
        $code2Label = $item2->getOptions();
        $code = $item2->getParsedValue();
        if(!empty($code) && $code != '0') $buf2[] = $code2Label[$code];

        // ここからはハードコード
        // buf1チェック
        if(count($buf1)) {
            if(in_array('新築', $buf1, true)) {
                $notDisplayLabels[] = '新築';  // 自身の非表示
                // 新築時選択不可である〇年以内を非表示
                foreach($item1->getOptions() as $value => $label) {
                    if(preg_match("/^\d{1,}年/", $label)) {
                        $notDisplayLabels[] = $label;
                    }
                }
            }
            if(in_array('築後未入居', $buf1, true)) {
                $notDisplayLabels[] = '築後未入居';  // 自身の非表示
            }
            if(in_array('新築を除く', $buf1, true)) {
                $notDisplayLabels[] = '新築';
                $notDisplayLabels[] = '築後未入居';
            }
            // 〇年以内が指定されていればその後方の〇年以内は非表示
            foreach($buf1 as $selVal) {
                if(!preg_match("/^\d{1,}年/" ,$selVal)) {
                    continue;
                }
                $notDispFlg = false;
                foreach($item1->getOptions() as $value => $label) {
                    if($notDispFlg) {
                        if(!in_array($label, $notDisplayLabels)) {
                            $notDisplayLabels[] = $label;
                        }
                    } else if($label == $selVal) {
                        $notDispFlg = true;
                    }
                }
                if($notDispFlg) {
                    if(!in_array('新築', $notDisplayLabels)) {
                        $notDisplayLabels[] = '新築';
                    }
                    $notDisplayLabels[] = '指定なし';
                }
            }
        }
        // buf2チェック
        if(count($buf2)) {
            if(!in_array('新築', $notDisplayLabels)) {
                $notDisplayLabels[] = '新築';
            }
            $selVal = $buf2[0];
            $notDispFlg = true;
            foreach($item2->getOptions() as $value => $label) {
                if(!preg_match("/^\d{1,}年/" ,$label)) {
                    continue;
                }
                if($label == $selVal) {
                    $notDispFlg = false;
                }
                if($notDispFlg) {
                    if(!in_array($label, $notDisplayLabels)) {
                        $notDisplayLabels[] = str_replace('以上', '以内', $label);
                    }
                }
            }
        }

        $itemValues = array_keys(array_flip($item1->getOptions()));

        if((count($itemValues) - 1) == count($notDisplayLabels)) {
            return $itemValues;
        }
        return $notDisplayLabels;
    }

    /**
     * li
     * @return \phpQueryObject
     */
    protected function _renderCheckItem($id, $name, $type, $value, $label, $checked, $disabled, $facetNum = null) {
        $dataId = $id;
        $id .= '-' . ($this->_idSuffix++);
        $li = pq('<li><label for="'.$id.'" data-id="'.$dataId.'"><span class="'.$type.'"></span></label></li>');
        $li['span']->append('<input id="'.$id.'" data-id="'.$dataId.'" type="'.$type.'" name="'.$name.'" value="'.$value.'"'.($checked?' checked':'').($disabled?' disabled':'').'>');
        $li['label']->append(sprintf('<span class="name">%s</span>',$label));
        if (is_numeric($facetNum)) {
            $li->append(pq('<span class="count"></span>')->text('('.number_format($facetNum).')'));
            if (!$facetNum) {
                $li['label']->addClass('tx-disable');
            }
        }
        if ($disabled) {
            // $li['label']->addClass('tx-disable');
        }
        return $li;
    }

    private function _getParsedValidCountForCategory($category){

        $count = 0;
        if( $category->category_id=='tatemono_kozo'||
            $category->category_id=='shumoku'      ||
            $category->category_id=='image'        ){

            foreach ($category->items as $item) {
                if ($item->getParsedValue() == true){
                    $count++;
                }
            }
        }elseif($category->category_id=='madori' ){
            $count =count($category->items[0]->getParsedValue());
        }
        return $count;
    }
}