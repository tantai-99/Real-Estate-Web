<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Lists\ArticleLinkType;
use Library\Custom\Model\Lists\CmsPlan;
?>
@extends('layouts.default')

@section('title', __('ページの作成/更新 （不動産お役立ち情報）'))

@section('script')
<script type="text/javascript" src="/js/app.site-map.js?v=2019121000"></script>
<script type="text/javascript">
$(function () {
	'use strict';
	
	app.SiteMap.MaxLevel = <?php echo HpPageRepository::MAX_LEVEL?>;
    app.SiteMap.MaxOriginalLarge = <?php echo HpPageRepository::MAX_ORIGINAL_LARGE?>;
    app.SiteMap.MaxOriginalSmall = <?php echo HpPageRepository::MAX_ORIGINAL_SMALL?>;
	app.SiteMap.Types = <?php echo json_encode($view->types)?>;
	app.SiteMap.TypeNames = <?php echo json_encode($view->typeNames)?>;
	app.SiteMap.Categories = <?php echo json_encode($view->categories)?>;
    app.SiteMap.CategoryNames = <?php echo json_encode($view->categoryNames)?>;
	
	app.SiteMap.FixedMenuTypes = <?php echo json_encode($view->fixedMenuTypes)?>;
	app.SiteMap.GlobalMenuTypes = <?php echo json_encode($view->globalMenuTypes)?>;
	app.SiteMap.GlobalMenuNumber = <?php echo json_encode($view->globalMenuNumber)?>;
	app.SiteMap.NotInMenuTypes = <?php echo json_encode($view->notInMenuTypes)?>;
	app.SiteMap.UniqueTypes = <?php echo json_encode($view->uniqueTypes)?>;
	
	app.SiteMap.HasDetailPageTypes = <?php echo json_encode($view->hasDetailPageTypes)?>;
	app.SiteMap.DetailPageTypes = <?php echo json_encode($view->detailPageTypes)?>;
	app.SiteMap.HasMultiPageTypes = <?php echo json_encode($view->hasMultiPageTypes)?>;
    app.SiteMap.ChildTypes = <?php echo json_encode($view->childTypes)?>;
	app.SiteMap.CategoryMap = <?php echo json_encode($view->categoryMap)?>;
    app.SiteMap.isSiteMapArticle = true;
    app.SiteMap.ChildTypesAdvancePlan = <?php echo json_encode($view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_ADVANCE')])?>;
    app.SiteMap.sideLayoutArticleType = <?php echo isset($view->sideLayoutArticle) ? $view->sideLayoutArticle['type'] : ArticleLinkType::SMALL_EXPAND;?>;
    app.SiteMap.sideLayoutArticleResult = <?php echo json_encode($view->sideLayoutArticleResult)?>;
    app.SiteMap.hasReserve = <?php echo getInstanceUser('cms')->getCurrentHp()->hasReserve();?>;
    app.SiteMap.isFirstCreatePageArticle = <?php echo (int)$view->isFirstCreatePageArticle;?>;
    app.SiteMap.IsTopOriginal = <?php echo $view->isTopOriginal;?>;
    app.SiteMap.IsAgency = <?php echo $view->isAgency;?>;
    <?php if ($view->isTopOriginal): ?>
        app.SiteMap.globalNav = <?php echo json_encode($view->globalNav)?>;
        // 2019/02/18 app.SiteMap.housingBlocksHidden = <?php // echo json_encode($this->housingBlocksHidden)?>; 
    <?php endif ?>
    var siteMapData = <?php echo json_encode($view->siteMapData)?>;
	var sitemap = app.SiteMap.init('<?php echo csrf_Token(false)?>', $('.main-contents-body'));
	sitemap.setData(siteMapData);
});

var all_upload_flg = <?php echo $view->allUploadFlg;?>;
</script>
@endsection

@section('content')
<!-- メインコンテンツ -->
<div class="main-contents sitemap-article-contents">
	<h1>ページの作成/更新 （不動産お役立ち情報）</h1>
	<div class="main-contents-body">
		<?php if($view->hp->hasChangedArticle()):?>
		<div class="alert-strong">
            本番に未反映の修正があります。公開設定から本番反映してください。
        </div>
		<?php endif;?>
		<?php if($view->allUploadFlg):?>
            <script type="text/javascript">
            $(function () {
                var contents = '' +
                    '<div style="margin: 40px 8px;">' +
                        '<p>現在共通設定が未反映の状態です。このページを修正・保存した場合、次回サイトの更新時に自動的に修正内容が反映されます。</p>' +
                        '<p>修正内容を即時反映させたくない場合は、先にサイトの公開/更新画面で公開処理を行ってから、編集を行ってください。</p>' +
                    '</div>';
                var modal = app.modal.popup({
                    contents: contents,
                    cancel: false,
                    closeButton: false,
                    autoRemove: false
                });
                modal.show();
                return false;
            });
            </script>
		<div class="alert-strong">
            現在共通設定が未反映の状態です。次回サイトの更新時に公開中のすべてのページ・リンクに「共通設定」の変更を反映する必要があります。
        </div>
		<?php endif;?>
		<div class="alert-normal">「サイトの公開/更新」メニューから公開/更新処理を行うまで、本番反映されません。<br>ページを作成するまで、メニュー表示されません。</div>
		
		<div class="edit-sitemap">
			<div class ="block-legend">
				<ul class="legend">
					<li><i class="i-e-edit"></i>編集</li>
					<li><i class="i-e-set"></i>操作</li>
				</ul>
				<!-- メインメニュー -->
				<h3>ページ構成<?php echo $view->toolTip('page_structure');?></h3>
            </div>
            <div class="block-legend-description">
                <p>追加ボタンから追加したいカテゴリーと記事を作成してください。</p>
                <a href="javascript:;" class="btn-use-page-article">利用可能な記事一覧を見る</a>
            </div>
            
            <div class="block-category">
                <div class="category-label large">
                    <div>大カテゴリー<?php echo $view->toolTip('large_category');?></div>
                </div>
                <div class="category-label small">
                    <div>小カテゴリー<?php echo $view->toolTip('small_category');?></div>
                </div>
                <div class="category-label article">
                    <div>記事<?php echo $view->toolTip('article_category');?></div>
                </div>
			</div>
			<div class="sitemap-main">
				<ul class="level1">
                    <li class="last">
						<div class="item add article">
							<a href="javascript:;">追加</a>
						</div>
					</li>
				</ul>
            </div>
			<!-- /メインメニュー -->

        </div>
        <div class="edit-sitemap sitemap-set-link">
            <div class ="block-legend">
				<h3>リンク設定</h3>
            </div>
            <div class="block-legend-description">
                <p>サイドコンテンツ（全ページ共通）に表示するリンクを選択してください。</p>
            </div>
            <div class="block-set-link">
                <dl>
                    <img src="../images/sitemap/set_link_result_<?php echo $view->sideLayoutArticle['type']?>.png" alt="">
                </dl>
                <dl>
                    <dd>選択中の設定：<span class="result-set-link">「<?php echo strip_tags($view->sideLayoutArticleResult[$view->sideLayoutArticle['type']]); ?>」</span></dd>
                    <dd><a class="btn-set-link btn-t-gray" href="javascript:;">設定を変更する</a></dd>
                </dl>
            </div>
        </div>
        <div class="sitemap-bottom">
            <a class="btn-delete-article" href="javascript:;">× 不要なページをまとめて削除する</a>
        </div>

    <div class="tempale-set-link" style="display:none;">    
        <div class="set-link-modal">
            <div class="block-legend">
                <h3>リンク設定<?php echo $view->toolTip('set_link_article');?></h3>
            </div>
            <div class="block-legend-description">
                <p>サイドコンテンツ（全ページ共通）に表示するリンクを選択してください。<br>作成したカテゴリーや記事のページ数に合わせて選択肢を選んでください。</p>
            </div>
            <div>
                <?php
                $articleLinkType = ArticleLinkType::getInstance()->getAll();
                foreach ($articleLinkType as $type=>$value) :
                ?>
                <div class="item-set-link">
                    <div class="label-set-link">
                    <label>
                        <input type="radio" name="type-set-link" value="<?php echo $type;?>"><?php echo $value;?><?php echo $view->toolTip('set_link_article_'.$type);?>
                    </label>
                    </div>
                    <div class="image-sample">
                        <img src="../images/sitemap/set_link_<?php echo $type?>.png" alt="">
                    </div>
                </div>
                <?php endforeach;?>
            </div>
        </div>
    </div>

    <div class="template-use-page" style="display:none;">
        <div class="content-article-use-page">
        <?php $plans = CmsPlan::getInstance()->getAll();?>
            <table class="tb-basic">
                <thead>
                    <tr>
                        <th>大カテゴリー</th>
                        <th>小カテゴリー</th>
                        <th>記事</th>
                        <th><?php echo $plans[config('constants.cms_plan.CMS_PLAN_ADVANCE')]?></th>
                        <th><?php echo $plans[config('constants.cms_plan.CMS_PLAN_STANDARD')]?></th>
                        <th><?php echo $plans[config('constants.cms_plan.CMS_PLAN_LITE')]?></th>
                    </tr>
                </thead>
            </table>
            <div class="table-body">
                <table class="tb-basic">
                    <!-- <thead>
                        <tr>
                            <th>大カテゴリ</th>
                            <th>小カテゴリ</th>
                            <th>記事</th>
                            <th><?php echo $plans[config('constants.cms_plan.CMS_PLAN_ADVANCE')]?></th>
                            <th><?php echo $plans[config('constants.cms_plan.CMS_PLAN_STANDARD')]?></th>
                            <th><?php echo $plans[config('constants.cms_plan.CMS_PLAN_LITE')]?></th>
                        </tr>
                    </thead> -->
                    <tbody>
                    <?php foreach($view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_ADVANCE')][100] as $large=>$largeCategory):?>
                        <?php foreach($view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_ADVANCE')][$largeCategory] as $small=>$smallCategory):?>
                            <?php foreach($view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_ADVANCE')][$smallCategory] as $article=>$articleCategory):?>
                            <tr>
                                <?php if ($small == 0 && $article == 0):?>
                                    <td class="large-column" data-type="<?php echo $largeCategory?>"><?php echo App::make(HpPageRepositoryInterface::class)->getTypeNameJp($largeCategory)?></td>
                                <?php endif;?>
                                <?php if ($article == 0):?>
                                <td class="small-column" data-type="<?php echo $smallCategory?>"><?php echo App::make(HpPageRepositoryInterface::class)->getTypeNameJp($smallCategory)?></td>
                                <?php endif;?>
                                <td class="article-column"><?php echo App::make(HpPageRepositoryInterface::class)->getTypeNameJp($articleCategory)?></td>
                                <td class="advance-column plan-use">•</td>
                                <?php 
                                $useStandard = '';
                                if (isset($view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_STANDARD')][$smallCategory])
                                    && in_array($articleCategory, $view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_STANDARD')][$smallCategory])) {
                                        $useStandard = '•';
                                    }
                                ?>
                                <td class="standar-column plan-use"><?php echo $useStandard;?></td>
                                <?php 
                                $useLite = '';
                                if (isset($view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_LITE')][$smallCategory])
                                    && in_array($articleCategory, $view->articlePageAllPlan[config('constants.cms_plan.CMS_PLAN_LITE')][$smallCategory])) {
                                        $useLite = '•';
                                    }
                                ?>
                                <td class="lite-column plan-use"><?php echo $useLite;?></td>
                            </tr>
                            <?php endforeach;?>
                        <?php endforeach;?>
                    <?php endforeach;?>
                    <tr>
                        <td>オリジナル大カテゴリー</td>
                        <td>オリジナル小カテゴリー</td>
                        <td>オリジナル記事</td>
                        <td class="plan-use">•</td>
                        <td class="plan-use">•</td>
                        <td class="plan-use">•</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
		
	</div>
</div>
<!-- /メインコンテンツ -->
<div class="btn-scroll">
    <div class="scroll-up is-disable"></div>
    <div class="scroll-down"></div>
</div>
@endsection