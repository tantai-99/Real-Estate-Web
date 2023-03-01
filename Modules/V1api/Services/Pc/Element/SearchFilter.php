<?php
namespace Modules\V1api\Services\Pc\Element;

use Library\Custom\Estate\Setting\SearchFilter\SearchFilterAbstract;
use Library\Custom\Model\Estate;
use Modules\V1api\Models;
use Modules\V1api\Services;

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

            Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_2,
            Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_3,
            Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_2,
            Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_2,
        ];
    }

    protected function isJoinedParticularType($type_id) {
        if (is_array($type_id)) {
            $type_id = Estate\TypeList::getInstance()->getCompositeType($type_id);
        }
        return in_array($type_id, $this->getJoinedParticularType());
    }

    public function renderTable($type_id, $doc) {
        $desiredElement = $this->createDesiredTableElement();
        $particularElement = $this->createParticularTableElement();
        if (!$this->isJoinedParticularType($type_id)) {
            $doc['.qp-desired-container']->append( $desiredElement );
            $doc['.qp-particular-container']->append( $particularElement );
        }
        else {
            if(count($particularElement['li'])) {
                // $tr = pq('<tr><th>こだわり条件</th><td><ul class="list-check three"></ul></td></tr>');
                // $tr['ul']->append($particularElement['li']);
                // $desiredElement->append($tr);
                $tr = pq('<table class="element-detail-table">
                <tbody><tr><th>こだわり条件</th><td><ul class="list-check three"></ul></td></tr></tbody></table>');
                $tr['ul']->append($particularElement['li']);
                $particularElement->append($tr);
                $doc['.qp-particular-container']->append($tr);
            }
            $doc['.qp-desired-container']->append( $desiredElement );
            //$doc['.qp-particular-container']->parent()->remove();
        }
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

        // こだわりモーダルのない種目は「こだわりモーダル」リンクを削除する
        if ($this->isJoinedParticularType($type_id)) {
            $asidetElem['.link-more-term']->remove();
        }
        // 特集
        if ($this->_searchFilter->isParsed()) {
            $asidetElem['.link-more-term']->find('.js-modal')->text('こだわり条件を表示');
        }
    }

    /**
     * こだわり条件モーダルを作成する。
     * @param $type_id
     * @param $total
     * @param $facet
     * @param $doc
     */
    public function renderKodawariModal($type_id, $total, $facet, $doc) {
        $searchFilterSection = $doc;
        if (!$this->isJoinedParticularType($type_id)) {
            $searchFilterSection->find('.contents-iframe.search-modal-detail .total-count')->text(number_format($total)."件");
            $this->_searchFilter->loadEnables($type_id);
            $searchFilterSection->find('.contents-iframe.search-modal-detail .num-and-btn:first')->after( $this->createParticularTableElement($facet) );
        }
    }

    /**
     * 希望条件を作成する
     */
    public function createDesiredTableElement() {
        return $this->_createTableElement( $this->_searchFilter->pickDesiredCategories(), self::SEARCHCATEGORY_DESIRED );
    }

    /**
     * こだわり条件を作成する
     */
    public function createParticularTableElement($facet = null) {
        return $this->_createTableElement( $this->_searchFilter->pickParticularCategories(), self::SEARCHCATEGORY_PARTICULAR, $facet );
    }

    /**
     * 希望条件を作成する[アサイド]
     * @param Modules\V1api\Services\BApi\Facet $facet
     */
    public function createDesiredAsideElement($facet = null) {
        return $this->_createAsideElement( $this->_searchFilter->pickDesiredCategories(), self::SEARCHCATEGORY_DESIRED, $facet );
    }

    /**
     * こだわり条件を作成する[アサイド]
     * @param int $estateType 物件種目
     * @param Modules\V1api\Services\BApi\Facet $facet
     */
    public function createParticularAsideElement($estateType, $facet = null) {
        $searchCategory = self::SEARCHCATEGORY_PARTICULAR;

        // 人気のこだわり条件
        $model = Models\PopularItemList::getInstance();
        $items = $model->get($estateType);
        if (!$items) {
            $items = [];
        }
        $itemsByCategory = [];

        $tmp = pq('<div/>');

        $section = pq('<section/>')->addClass('select-term detail-side');
        $ul = pq('<ul/>');
        foreach ($items as $itemInfo) {
            // 選択中のこだわりチェック用に[category_id][item_id]配列作成
            $itemsByCategory[$itemInfo[0]][$itemInfo[1]] = true;

            $category = $this->_searchFilter->getCategory($itemInfo[0]);
            if (!$category) {
                continue;
            }
            $item = $category->getItem($itemInfo[1]);
            if (!$item) {
                continue;
            }
            // CMS特集の条件に設定されている場合は表示しない
            if ($item->getParsedValue()==true) {
                continue;
            }

            $li = $this->_renderItem($category, $item, $searchCategory, $facet);
            if ($li) {
                $ul->append($li);
            }
        }
        if ($ul->children()->length) {
            if (!$this->isJoinedParticularType($estateType)) {
                $title = '人気のこだわり条件';
                //特集の場合は「人気のこだわり条件」を表示しない
                if($this->_searchFilter->isParsed()){
                    // 複合種目の場合は表示
                    if (!(is_array($estateType) && count($estateType) !== 1)) {
                        $title='';
                    }
                }
            }
            else {
                $title = '人気のこだわり条件';
                $section->attr('style','border:0 none;');
            }

            if ( !empty($title) ) {
                $section->append(pq('<h4/>')->addClass('articlelist-side-heading2')->text( $title ));
                $section->append($ul);
                $tmp->append($section);
            }
        }

        // 選択中のこだわり条件
        $section = pq('<section/>')->addClass('select-term detail-side');
        $ul = pq('<ul/>');
        foreach ($this->_searchFilter->pickParticularCategories() as $category) {
            foreach ($category->items as $item) {
                // 人気のこだわりの場合無視
                if (!empty($itemsByCategory[$category->category_id][$item->item_id])) {
                    if (!$this->_searchFilter->isParsed()) {
                        continue;
                    } else if (!$item->item_value) {
                        continue;
                    }
                }
                // CMS特集の条件に設定されている場合は表示しない
                if ($item->getParsedValue()==true) {
                    continue;
                }
                $li = $this->_renderItem($category, $item, $searchCategory, $facet);
                if ($li) {
                    $ul->append($li);
                    // 未選択用にhiddenパラメータをセット
                    $section->append('<input type="hidden" name="'."search_filter[{$category->category_id}][{$item->item_id}]".'" value="0">');
                }
            }
        }
        if ($ul->children()->length) {
            $section->append(pq('<h4/>')->addClass('articlelist-side-heading2')->text( '選択中のこだわり条件' ));
            $section->append($ul);
            $tmp->append($section);
        }

        // モーダルとidが衝突してるので回避
        $detailSide[0] = $tmp['.detail-side:eq(0)'];
        $detailSide[1] = $tmp['.detail-side:eq(1)'];
        foreach($detailSide as $key => $value){
            if ($value) {
                $str = '';
                $str = str_replace(' id="', ' id="aside-', $value->html());
                $str = str_replace(' for="', ' for="aside-', $str);
                $tmp[".detail-side:eq($key)"]->html($str);
            }
        }


        return $tmp->children();
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

    /**
     *
     * @param array $categories
     * @param string $searchCategory
     * @param Modules\V1api\Services\BApi\Facet $facet
     */
    protected function _createTableElement($categories, $searchCategory, $facet = null) {
        $table = pq('<table/>')->addClass('element-detail-table');
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
                        $aliasShumokus = Services\BApi\SearchFilterTranslator::getShumokuAliasMapStatic($shumokuCategoryItem->item_id);
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
            
            if($category->category_id == 'shumoku') {
                foreach($category->items as $shumokuCategoryItem) {
                    if ($shumokuCategoryItem->item_id == '39') {
                        $shumokuCategoryItem->item_value = 1;
                    }
                }
            }

            if (!$this->_isDispAsideCategory($category)){
                continue;
            }

            if ($category->category_id == 'menseki') {
                foreach ($category->items as $item) {

                    if($item->item_id == 3 || $item->item_id == 4) continue;

                    $tr = pq('<tr><th></th><td></td></tr>');
                    $tr['th']->text( $item->item_id == 1 ? $category->getLabel() : $item->getLabel() );
                    $tr['td']->append( $this->_renderListItem($category, $item, $searchCategory, $facet) );
                    $table->append($tr);
                }
                continue;
            }

            $tr = pq('<tr><th></th><td></td></tr>');
            $tr['th']->text( $category->getLabel() );
            // （?）アイコン
            if ($category->category_id == 'keiyaku_joken') {
                $tr['th']->append($this->_renderKeiyakuJokenToolTip());
            }
            else if ($category->category_id == 'tatemono_kozo') {
                $tr['th']->append($this->_renderTatemonoKozoToolTip());
            }

            $this->_renderItems($tr['td'], $category, $searchCategory, false, $facet);
            if (!$tr['td']->children()->length) {
                continue;
            }
            $table->append($tr);
        }
        return $table;
    }

    /**
     *
     * @param array $categories
     * @param string $searchCategory
     * @param Modules\V1api\Services\BApi\Facet $facet
     */
    protected function _createAsideElement($categories, $searchCategory, $facet = null) {
        $tmp = pq('<div/>');

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
                        $aliasShumokus = Services\BApi\SearchFilterTranslator::getShumokuAliasMapStatic($shumokuCategoryItem->item_id);
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

                    $section = pq('<section/>')->addClass('select-term');
                    $h4 = pq('<h4/>')->addClass('articlelist-side-heading2')
                                     ->text( $item->item_id == 1 ? $category->getLabel() : $item->getLabel() );
                    $section->prepend($h4);
                    $section->append( $this->_renderListItem($category, $item, $searchCategory, $facet) );
                    $tmp->append($section);
                }
                continue;
            }

            $section = pq('<section/>')->addClass('select-term');
            $this->_renderItems($section, $category, $searchCategory, true, $facet);
            if (!$section->children()->length) {
                continue;
            }
            $h4 = pq('<h4/>')->addClass('articlelist-side-heading2')->text( $category->getLabel() );
            // （?）アイコン
            if ($category->category_id == 'keiyaku_joken') {
                $h4->append($this->_renderKeiyakuJokenToolTip());
            }
            else if ($category->category_id == 'tatemono_kozo') {
                $h4->append($this->_renderTatemonoKozoToolTip());
            }
            $section->prepend($h4);
            $tmp->append($section);
        }
        $tmp->find('.list-check')->removeClass('list-check');
        return $tmp->children();
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
        //アピール・オープンルーム・モデルルーム・最適用途・現地販売会
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


            // 特集でなければ表示する
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




    protected function _renderKeiyakuJokenToolTip() {
        $content = <<< EOM
一般の賃貸契約とは異なり契約期間満了によって契約が終了し、契約更新は行われません。<br>
契約期間は物件によって異なります。貸主との合意があれば再契約は可能ですが、賃料等の賃貸条件の変更や、敷金・礼金・仲介手数料等があらためて発生する場合がございます。お問合せの際に十分ご確認ください。
EOM;
        return $this->_renderToolTip('定期建物賃貸借のこと', $content);
    }

    protected function _renderTatemonoKozoToolTip() {
        $content = <<< EOM
<span class="bold">●鉄筋系</span><br>
「RC（鉄筋コンクリート）」「SRC（鉄骨鉄筋コンクリート）」「PC（プレキャストコンクリート）」の建物を検索します。<br>
<span class="bold">●鉄骨系</span><br>
「軽量鉄骨」「鉄骨造」「重量鉄骨造」「HPC（鉄骨プレキャストコンクリート造）」「ALC（軽量気泡コンクリート）」の建物を検索します。<br>
<span class="bold">●木造</span><br>
「木造」の建物を検索します。<br>
<span class="bold">●その他</span><br>
「ブロック」「鉄筋ブロック造」「CFT（コンクリート充鎮鋼管造）」「その他」の建物を検索します。
EOM;
        return $this->_renderToolTip('建物構造のこと', $content);
    }

    protected function _renderToolTip($title, $content) {
        return pq(' <span class="tooltip"><i class="tooltip-icon"></i><div class="tooltip-body"><p><span class="tooltip-heading">'.$title.'</span>'.$content.'</p></div></span>');
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
                $selectContainer = pq('<div/>')->addClass('select-price')->appendTo($container);
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

        $ul = pq('<ul/>')->addClass('list-check');
        $ul->appendTo($container);

        if (isset($kakaku_item)) {
            $ul->append('<li>' . implode('</li><li>', $kakaku_item) . '</li>');
        }

        foreach ($items as $item) {

            // 築年数-以上、面積&土地面積はフォーム表示しない
            if($category->category_id == 'chikunensu' && $item->item_id == 2) {
                continue;
            }

            // アサイドの場合築年数、駅からの徒歩はセレクトボックス
            if ($aside && $category->category_id == 'eki_toho_fun') {
                $itemElem = $this->_renderListItem($category, $item, $searchCategory, $facet);
            } 
            else if($aside && $category->category_id == 'chikunensu' && $item->item_id == 1) {
                $itemElem = $this->_renderListItem($category, $item, $searchCategory, null);

				if(count($itemElem->find('option')) == 0) {
					 $itemElem = null;
				}
            }
            else if($category->category_id == 'chikunensu' && $item->item_id == 1) {
                $setZeroFlg = false;
                if(count($item->item_value) == 0) {
                    $setZeroFlg = true;
                    $item->item_value[] = '0';
                }
                $itemElem = $this->_renderRadioItem($category, $item, $searchCategory, null);

				if(count($itemElem->find('li')) == 0) {
					$itemElem = null;
				}

                if($setZeroFlg = true) {
                    $item->item_value = [];
                }
            }
            else {
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
     * @return phpQueryObject
     */
    protected function _renderItem($category, $item, $searchCategory, $facet = null) {
        switch ($item->getType()) {
            case 'list':
                return $this->_renderListItem($category, $item, $searchCategory, $facet);
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
     * @return phpQueryObject
     */
    protected function _renderListItem($category, $item, $searchCategory, $facet = null) {

        $addClass = '';
        if ($searchCategory == self::SEARCHCATEGORY_DESIRED && $this->_searchFilter->isParsed()) {
            // 特集からの遷移かつ、条件次第で変更が必要な場合
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
                     $category->category_id=='joho_kokai' ){
                foreach ($item->getOptions() as $value => $label) {
                    if ( ($value <= $item->getParsedValue() && $value) ||
                        !$item->getParsedValue()) {
                        $options[$value] = $label;
                    }
                }
            } else if ($category->category_id=='chikunensu') {
				$notDisplayLabels = $this->_getNotDisplayLabels($category);

				foreach ($item->getOptions() as $value => $label) {
					if(!in_array($label, $notDisplayLabels)) {
						$options[$value] = $label;
					}
				}
/*
                $shinchiku_hide_flag = false;
                foreach ($item->getOptions() as $value => $label) {
                    $shinchiku_hide_flag = false;
                    if ( ($value <= $item->getParsedValue()) ||
                       !$item->getParsedValue()) {
                       $options[$value] = $label;
                    }

                    // 新築除くがcmsで設定されている場合
                    if ($label == '新築') {
                        $shinchiku_show_value = $value;
                    }
                    if ( ($item->getParsedValue() == $value) && ($label == '新築を除く')){
                        $shinchiku_hide_flag = true;
                        if (isset($options[$shinchiku_show_value])) {
                            unset($options[$shinchiku_show_value]);
                        }
                    }
                }
                // cmsで新築除く以外が設定されている場合は指定なし除く
                if ($item->getParsedValue() && !$shinchiku_hide_flag) {
                    unset($options['']);
                    unset($options[0]);
                }
*/
            } else {
                foreach ($item->getOptions() as $value => $label) {
                    if (!$value || $value == $item->getParsedValue()) {
                        $options[$value] = $label;
                    }
                }
            }

            if(gettype($item->item_value) == 'array') {
                if(count($item->item_value) > 0) {
				    $selected = $item->item_value[0];
                } else if(count($item->getParsedValue()) > 0) {
					$parsedVals = $item->getParsedValue();
				    $selected = $parsedVals[ count($parsedVals) - 1 ];

					if($item->getOptions()[ $selected ] == '新築を除く' && count($parsedVals) >= 2) {
                        $selected = $parsedVals[ count($parsedVals) - 2 ];
                    }
                } else {
                    $selected = 0;
                }
            } else {
                $selected = $item->item_value;
                if($this->_searchFilter->isValueEmpty()){
                    $selected = $item->getParsedValue();
                }
            }

        }
        else if(gettype($item->item_value) == 'array') {
            if($category->category_id=='chikunensu') {
                $selected = 0;
                if(count($item->item_value) > 0) {
                    $selected = $item->item_value[0];
                }
                $options = $item->getOptions();
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
            $addClass = $disabled? 'sp-disable': '';

        }
        else {
            $disabled = !$item->isLoaded();
        }
        // 仕様変更（2016年9月）
        $disabled = false;
        if(gettype($item->item_value) == 'array') {
            $select = pq('<select name="search_filter['.$category->category_id.']['.$item->item_id.'][]"'.($disabled?' disabled':'').($addClass?' class='.$addClass: '').'></select>');
        } else {
            $select = pq('<select name="search_filter['.$category->category_id.']['.$item->item_id.']"'.($disabled?' disabled':'').($addClass?' class='.$addClass: '').'></select>');
        }
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
     * @return phpQueryObject
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
     * @return phpQueryObject
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
     * @return phpQueryObject
     */
    protected function _renderFlagItem($category, $item, $searchCategory, $facet = null) {

        $addClass = '';
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
            //$addClass = $disabled? 'sp-disable': '';
        }

        return $this->_renderCheckItem(
                        "{$category->category_id}-{$item->item_id}",
                        "search_filter[{$category->category_id}][{$item->item_id}]",
                        'checkbox',
                        1,
                        $label,
                        $checked,
                        $disabled,
                        $facetNum,
                        $addClass);
    }

    /**
     * li
     * @param Library\Custom\Estate\Setting\SearchFilter\Category\Category $category
     * @param Library\Custom\Estate\Setting\SearchFilter\Item\Radio $item
     * @param string $searchCategory
     * @param string $type
     * @param Modules\V1api\Services\BApi\Facet $facet
     * @return phpQueryObject
     */
    protected function _renderMultiCheckItem($category, $item, $searchCategory, $type, $facet = null) {
        $facetMap = null;
        if ($facet) {
            $facetMap = $facet->getFacet($category->category_id, $item->item_id);
        }
        $addClass = '';

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

        $ul = pq('<ul/>')->addClass('list-check');
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
                    // 新築除くがチェックされているときは指定なしがデフォルト
/*
                    if ($category->category_id == 'chikunensu' && !$shinchiku_show_flag && !$value) {
                        $checked = true;
                    }
*/
					if($category->category_id == 'chikunensu') {
					}
					
                }
                if ($searchCategory == self::SEARCHCATEGORY_DESIRED && $this->_searchFilter->isParsed()) {
                    //情報公開日
                    //・CMSで特集条件を設定値以下を表示する(指定なしは表示しない)
                    if ($category->category_id == 'joho_kokai' ||
                      $category->category_id == 'eki_toho_fun') {
                        $disabled = true;
                        if( $item->getParsedValue() &&
                          (!$value || $value > $item->getParsedValue())) {
                            continue;
                        }
                    } else if ($category->category_id == 'chikunensu') {
                        $disabled = true;
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
                            $facetNum,
                            $addClass);
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
     * @return phpQueryObject
     */
    protected function _renderCheckItem($id, $name, $type, $value, $label, $checked, $disabled, $facetNum = null, $addClass = '') {
        $dataId = $id;
        $id .= '-' . ($this->_idSuffix++);
        $li = pq('<li/>');
        $li->append('<input id="'.$id.'" data-id="'.$dataId.'" type="'.$type.'" name="'.$name.'" value="'.$value.'"'.($checked?' checked':'').($disabled?' disabled':'').($addClass?' class='.$addClass: '').'>');
        $l = pq('<label for="'.$id.'" data-id="'.$dataId.'"></label>')->text($label);
        if (is_numeric($facetNum)) {
            $l->append(pq('<span class="count"></span>')->text('('.number_format($facetNum).')'));
            if (!$facetNum) {
               $l->addClass('tx-disable');
            }
        }
        if ($disabled) {
            $l->addClass('tx-disable');
        }
        $li->append($l);
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

