<?php

use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Estate\TypeList;

?>
@extends('layouts.default')

@section('title', __('ページの作成/更新'))

@section('script')
<script type="text/javascript" src="/js/libs/jquery.pagination.js"></script>
<script type="text/javascript" src="/js/app.site-map.js?v=2019121000"></script>
<script type="text/javascript" src="/js/app.link-house.js"></script>
<?php if ($view->hasSearchSetting) : ?>
    <script type="text/javascript" src="/js/app.estate.js?v=2021031500"></script>
    <link href="/css/estate_extension.css" media="screen" rel="stylesheet" type="text/css">
<?php endif ?>

<script type="text/javascript">
    $(function() {
        'use strict';

        app.SiteMap.MaxLevel = <?php echo HpPageRepository::MAX_LEVEL ?>;
        app.SiteMap.Types = <?php echo json_encode($view->types) ?>;
        app.SiteMap.TypeNames = <?php echo json_encode($view->typeNames) ?>;
        app.SiteMap.Categories = <?php echo json_encode($view->categories) ?>;
        app.SiteMap.CategoryNames = <?php echo json_encode($view->categoryNames) ?>;

        app.SiteMap.FixedMenuTypes = <?php echo json_encode($view->fixedMenuTypes) ?>;
        app.SiteMap.GlobalMenuTypes = <?php echo json_encode($view->globalMenuTypes) ?>;
        app.SiteMap.GlobalMenuNumber = <?php echo json_encode($view->globalMenuNumber) ?>;
        app.SiteMap.NotInMenuTypes = <?php echo json_encode($view->notInMenuTypes) ?>;
        app.SiteMap.UniqueTypes = <?php echo json_encode($view->uniqueTypes) ?>;

        app.SiteMap.HasDetailPageTypes = <?php echo json_encode($view->hasDetailPageTypes) ?>;
        app.SiteMap.DetailPageTypes = <?php echo json_encode($view->detailPageTypes) ?>;
        app.SiteMap.HasMultiPageTypes = <?php echo json_encode($view->hasMultiPageTypes) ?>;
        app.SiteMap.ChildTypes = <?php echo json_encode($view->childTypes) ?>;
        app.SiteMap.CategoryMap = <?php echo json_encode($view->categoryMap) ?>;
        app.SiteMap.AllCategoryArticle = <?php echo json_encode($view->allCategoryArticlePage); ?>;
        app.SiteMap.allTypeArticlePage = <?php echo json_encode($view->allTypeArticlePage); ?>;
        app.SiteMap.ToolTipTitle = <?php echo json_encode($view->toolTip('page_list_title')); ?>;
        app.SiteMap.ToolTipUpdateDate = <?php echo json_encode($view->toolTip('page_list_update_date')); ?>;
        app.SiteMap.ToolTipSearchSpecialLabel = <?php echo json_encode($view->toolTip('search_special_label')); ?>;

        app.SiteMap.isSiteMapArticle = false;

        app.SiteMap.IsTopOriginal = <?php echo $view->isTopOriginal; ?>;
        app.SiteMap.IsAgency = <?php echo $view->isAgency; ?>;
        <?php if ($view->isTopOriginal) : ?>
            app.SiteMap.globalNav = <?php echo json_encode($view->globalNav) ?>;
            // 2019/02/18 app.SiteMap.housingBlocksHidden = <?php // echo json_encode($this->housingBlocksHidden)
                                                            ?>; 
        <?php endif ?>
        var siteMapData = <?php echo json_encode($view->siteMapData) ?>;
        var estateSiteMapData = <?php echo $view->estateSiteMapData ? json_encode($view->estateSiteMapData) : 'null' ?>;
        var siteMapIndexData = <?php echo json_encode($view->siteMapIndexData) ?>;

        var sitemap = app.SiteMap.init('<?php echo csrf_Token(false) ?>', $('.main-contents-body'));
        if (estateSiteMapData) {
            sitemap.setEstateData(estateSiteMapData);
        }
        sitemap.setIndexData(siteMapIndexData);
        sitemap.setData(siteMapData);
        app.SiteMap.hasSearchSetting = <?php echo $view->hasSearchSetting ?>;
        var Master = null;
        var baseSettings = null;
        var setting = null;
        var isLite = <?php echo (int) ($view->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE')); ?>;
        app.SiteMap.isLite = isLite;
        <?php if ($view->hasSearchSetting) : ?>
            Master = {
                prefMaster: <?php echo json_encode($view->prefMaster) ?>,
                searchTypeMaster: <?php echo json_encode($view->searchTypeMaster) ?>,
                SearchTypeCondition: <?php echo json_encode($view->searchTypeConditionMaster) ?>,
                searchTypeDirectMaster: <?php echo json_encode($view->searchTypeDirectMaster) ?>,
                searchTypeConst: <?php echo json_encode($view->searchTypeConst) ?>,
                estateTypeMaster: <?php echo json_encode($view->estateTypeMaster) ?>,
                shumokuTypeMaster: <?php echo json_encode($view->shumokuTypeMaster) ?>,
                specialPublishEstateMaster: <?php echo json_encode($view->specialPublishEstateMaster) ?>,
                specialTesuryoKokokuhiMaster: <?php echo json_encode($view->specialTesuryoKokokuhiMaster) ?>,
                specialSearchPageTypeMaster: <?php echo json_encode($view->specialSearchPageTypeMaster) ?>
            };
            baseSettings = <?php echo json_encode($view->baseSettings) ?>;
            setting = <?php echo json_encode($view->specialSetting) ?>;
        <?php endif ?>
        app.SiteMap.LinkHouse = app.LinkHouse.init(baseSettings, Master, setting, isLite);
        <?php if ($view->isTopOriginal) : ?>
            app.notify('/data-link/api-get-update-navigation');
        <?php endif ?>
    });

    var all_upload_flg = <?php echo $view->allUploadFlg; ?>;
</script>
@endsection
<!-- メインコンテンツ -->
@section('content')
<div class="main-contents">
    <h1>ページの作成/更新</h1>
    <div class="main-contents-body">
        <?php foreach ($view->hp->getEstateContactNecessity() as $estateClassName) : ?>
            <div class="alert-strong">物件問合せ（<?php echo $estateClassName ?>）が未設定です</div>
        <?php endforeach; ?>
        <?php foreach ($view->hp->getEstateRequestNecessity() as $estateClassName) : ?>
            <div class="alert-strong">物件リクエスト（<?php echo $estateClassName ?>）が未設定です</div>
        <?php endforeach; ?>

        <?php if ($view->hp->hasChanged()) : ?>
            <div class="alert-strong">
                本番に未反映の修正があります。公開設定から本番反映してください。
            </div>
        <?php endif; ?>
        <?php if ($view->allUploadFlg) : ?>
            <div class="alert-strong">
                現在共通設定が未反映の状態です。次回サイトの更新時に公開中のすべてのページ・リンクに「共通設定」の変更を反映する必要があります。
            </div>
        <?php endif; ?>
        <div class="alert-normal">「サイトの公開/更新」メニューから公開/更新処理を行うまで、本番反映されません。<br>ページを作成するまで、メニュー表示されません。</div>


        <div class="edit-sitemap">
            <div class="block-legend">
                <ul class="legend">
                    <li><i class="i-e-edit"></i>編集</li>
                    <li><i class="i-e-set"></i>操作</li>
                    <li><i class="i-e-list"></i>一覧</li>
                </ul>
                <!-- メインメニュー -->
                <h3>メインメニュー</h3>
            </div>
            <div class="sitemap-main">
                <h4>グローバルメニュー</h4>
                <ul class="level1">
                    <li class="last">
                        <div class="item add">
                            <a href="">追加</a>
                        </div>
                    </li>
                </ul>
            </div>
            <!-- /メインメニュー -->

            <hr>

            <!-- 固定メニュー -->
            <h3>固定メニュー</h3>
            <div class="sitemap-fix">
                <ul class="level1">
                </ul>
            </div>
            <!-- /固定メニュー -->

        </div>



        <div class="edit-sitemap free">
            <!-- 階層外のページ -->
            <h3>階層外のページ<?php echo $view->toolTip('site_map'); ?></h3>
            <div class="sitemap-fix outer">
                <ul class="level1">
                    <li class="last">
                        <div class="item add last">
                            <a href="javascript:;" class="btn-modal">追加</a>
                        </div>
                    </li>
                </ul>
            </div>

            <?php if ($view->cms_plan !== config('constants.cms_plan.CMS_PLAN_LITE')) : ?>
                <h3>特集ページ</h3>
                <div class="sitemap-fix">
                    <ul class="level1">
                    </ul>
                </div>
            <?php endif; ?>
            <!-- /階層外のページ -->

        </div>


    </div>
</div>
<?php if ($view->hasSearchSetting) : ?>
    <div id="template_modal" style="display: none">
        <h2 class="individual-title">種目を選択してください。</h2>
        <div id="enabled_estate_type">
            <?php $baseEstateTypes = $view->form->getElement('enabled_estate_type')->getValueOptions(); ?>
            <?php $name = 'estate_class' ?>
            <?php $estateClassRadios = explode('<br>', $view->form->form($name, false)) ?>
            <ul class="is-required">
                <?php $i = 0; ?>
                <?php foreach ($view->form->getElement("$name")->getValueOptions() as $estateClass => $estateClassLabel) : ?>
                    <li class="<?php if ($i != 0) : ?>mt10<?php endif; ?>">
                        <?php echo $estateClassRadios[$i++] ?>
                        <ul class="ml20">
                            <?php foreach (TypeList::getInstance()->getByClass($estateClass) as $estateType => $estateTypeName) : ?>
                                <?php if (!isset($baseEstateTypes[$estateType])) continue ?>
                                <li style="display: inline-block">
                                    <label>
                                        <input data-estate-class="<?php echo $estateClass ?>" type="checkbox" name="enabled_estate_type[]" value="<?php echo $estateType ?>">
                                        <?php echo h($estateTypeName) ?>
                                    </label>
                                    <?php if (isset($view->shumokuTypeMaster[$estateType])) { ?>
                                        <a style="margin-left:-43px; margin-right:10px;">詳細な種目を選ぶ</a>
                                    <?php } ?>
                                    <?php if (isset($view->shumokuTypeMaster[$estateType])) { ?>
                                        <div class="shumoku_shosai_box" style="nowrap">
                                            <?php
                                            $cnt = 0;
                                            foreach ($view->shumokuTypeMaster[$estateType] as $item) {
                                            ?>

                                                <?php if (gettype($item) == 'string') {
                                                    echo $item;
                                                } else { ?>
                                                    <label style="display: block;float: left;">
                                                        <input class="shumoku_shosai" type="checkbox" value="<?php echo $item['item_id']; ?>" <?php print ' initialck="' . $item['checked'] . '"'; ?> label_val="<?php echo $item['label']; ?>">
                                                        <?php echo $item['label']; ?>
                                                    </label>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if ($estateType == 12) { ?>
                                                <br style="clear:both;" />
                                                <label style="display: block;clear: both;"><b>オーナーチェンジ</b></label>

                                                <label style="display: block;float: left;">
                                                    <input type="radio" name="owner_change" value="0" checked>オーナーチェンジを含む
                                                </label>
                                                <label style="display: block;float: left;">
                                                    <input type="radio" name="owner_change" value="2">オーナーチェンジを除く
                                                </label>
                                                <label style="display: block;float: left;">
                                                    <input type="radio" name="owner_change" value="1">オーナーチェンジのみ
                                                </label>
                                            <?php } ?>
                                        </div>

                                    <?php } ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="errors"></div>
        </div>
        <h2 class="individual-title">設定方法を選択してください。</h2>
        <div class="js-method-search">
            <ul class="is-required">
                <?php foreach ($view->searchTypeConditionMaster as $key => $searchType) : ?>
                    <li><label><input type="radio" name="search_type[]" value="<?php echo $key; ?>"><?php echo $searchType; ?></label></li>
                <?php endforeach; ?>
            </ul>
            <div class="errors"></div>
        </div>
        <h2 class="individual-title">都道府県を選択してください。</h2>
        <div id="pref" class="prefectures">
            <div class="errors"></div>
        </div>
        <h2 class="individual-title">公開する物件の種類を選択してください。</h2>
        <div class="js-type-publish">
            <div class="is-required" id="publish_estate">
                <p class="list-heading">公開する物件の種類<br>
                <ul class="list-radio-block">
                    <?php $checks = explode('<br>', $view->formMethod->form('publish_estate', false)) ?>
                    <li>
                        <?php echo $checks[0] ?>
                    </li>
                    <li>
                        <?php echo preg_replace('/\<\/label\>$/', '<span class="fs-small" style="display:inline;">※ATBBの物件情報入手にて「取込み」し公開した物件</span></label>', $checks[1]) ?>
                    </li>
                    <?php $isAllowedSecondEstate = getInstanceUser('cms')->isAvailableSecondEstate() && $view->acl()->isAllowed('index', 'second-estate-search-setting') ?>
                    <?php if ($isAllowedSecondEstate) : ?>
                        <li>
                            <?php echo $checks[2] ?>
                        </li>
                    <?php endif; ?>
                </ul>
                <p class="list-heading">公開する物件の絞り込みオプション
                    <?php if ($isAllowedSecondEstate) : ?>
                        <br>
                        <span class="fs-small">※2次広告自動公開の物件が選択されている場合はこのオプションは利用できません。</span>
                    <?php endif; ?>
                </p>
                <ul class="list-radio-block">
                    <li><?php echo $checks[3] ?></li>
                </ul>
                <div class="errors ml0"></div>
            </div>
        </div>
        <h2 class="individual-title">手数料/広告費を選択してください。</h2>
        <div class="tesuryo_kokokuhi">
            <?php $name = 'tesuryo_kokokuhi' ?>
            <?php $checks = explode('<br>', $view->formMethod->form($name, false)) ?>
            <div>
                <?php echo $checks[0] ?>
            </div>

            <div class="sp-basic-tesuryo">
                <label><input type="checkbox" id="tesuryo_check" />手数料ありの物件だけ表示する</label>
                <span>
                    (
                    <?php
                    $radio1 =  preg_replace('/checkbox/', 'radio', $checks[1]);
                    $radio1 =  preg_replace('/手数料ありの物件だけ表示する（(.*)）/', "$1", $radio1);
                    $radio2 =  preg_replace('/checkbox/', 'radio', $checks[2]);
                    $radio2 =  preg_replace('/手数料ありの物件だけ表示する（(.*)）/', "$1", $radio2);
                    echo $radio1;
                    echo $radio2;
                    ?>)
                </span>
            </div>

            <div>
                <?php echo $checks[3] ?>
            </div>
            <div class="errors"></div>
        </div>
    </div>
<?php endif ?>
<!-- /メインコンテンツ -->
@endsection