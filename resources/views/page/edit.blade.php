@php
    $layout = 'layouts.default';
    if (isset($view->layout)) {
        $layout = 'layouts.'.$view->layout;
    }
@endphp
@extends($layout)
@section('style')
<link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">
@endsection
@section('script')
<script type="text/javascript" src="/js/libs/jquery-ui.datepicker.min.js"></script>
<script type="text/javascript" src="/js/libs/jquery-ui.datepicker-ja.js"></script>
<script type="text/javascript" src="/js/libs/jquery.pagination.js"></script>
<script type="text/javascript" src="/js/upload.js"></script>
<script type="text/javascript" src="/js/libs/intersection-observer.js"></script>
<script type="text/javascript" src="/js/libs/lazyload.js"></script>

<?php $gmapApiChannel = \Library\Custom\Hp\Map::getGoogleMapChannelByProfile();?>
<?php $gmapApiChannel = ($gmapApiChannel) ? "&".$gmapApiChannel : "";?>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=quarterly&<?php echo \Library\Custom\Hp\Map::getGooleMapKey().$gmapApiChannel ?>&sensor=false&libraries=places"></script>
<script type="text/javascript" src="/js/libs/jquery.selection.js"></script>
<script type="text/javascript" src="/js/libs/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="/js/page.js"></script>
<script type="text/javascript" src="/js/page.sample.js"></script>
<script type="text/javascript" src="/js/page.freeword.js"></script>
<script type="text/javascript" src="/js/page.restrict.js"></script>

<?php if(isCurrent(null, 'initialize')):?>
<script type="text/javascript" src="/js/initialize.js"></script>
<?php endif;?>
<script type="text/javascript" src="/js/page.change.js"></script>
<script type="text/javascript" src="/js/app.link-house.js"></script>
<?php if ($view->hasSearchSetting):?>
<script type="text/javascript" src="/js/app.estate.js?v=2021031500"></script>
@push('style')
<link href="/css/estate_extension.css" media="screen" rel="stylesheet" type="text/css">
@endpush
<?php endif ?>
<script type="text/javascript">
app.token = '<?php echo csrf_token(false) ?>';
app.page.info = <?php echo json_encode($view->page->getEditInfo())?>;
app.page.sampleImageMap = <?php echo isset($view->sampleImageMap) ? json_encode($view->sampleImageMap): 'null'?>;
app.page.hasSearchSetting = <?php echo $view->hasSearchSetting?>;
$(function () {
    'use strict';
    var Master = null;
    var baseSettings = null;
    var setting = null;
    var isLite = <?php echo (int) ($view->page->getCompany()->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE'));?>;
    $("input[type='checkbox']").on('click', function(e){
        if ($(this).is(':checked')) {
            $(this).parent().find("input[type='hidden']").val(1);
        } else {
            $(this).parent().find("input[type='hidden']").val(0);
        }
    });
    <?php if ($view->hasSearchSetting):?>
    Master = {
			prefMaster                 : <?php echo json_encode($view->prefMaster)?>,
            searchTypeMaster           : <?php echo json_encode($view->searchTypeMaster)?>,
            SearchTypeCondition        : <?php echo json_encode($view->searchTypeConditionMaster)?>,   
			searchTypeDirectMaster     : <?php echo json_encode($view->searchTypeDirectMaster)?>,
			searchTypeConst            : <?php echo json_encode($view->searchTypeConst)?>,
			estateTypeMaster           : <?php echo json_encode($view->estateTypeMaster)?>,
			shumokuTypeMaster          : <?php echo json_encode($view->shumokuTypeMaster)?>,
			specialPublishEstateMaster : <?php echo json_encode($view->specialPublishEstateMaster)?>,
            specialTesuryoKokokuhiMaster : <?php echo json_encode($view->specialTesuryoKokokuhiMaster)?>,
			specialSearchPageTypeMaster: <?php echo json_encode($view->specialSearchPageTypeMaster)?>
		};
    baseSettings = <?php echo json_encode($view->baseSettings)?>;
    setting = <?php echo json_encode($view->specialSetting)?>;
    <?php endif ?>
    app.LinkHouse.init(baseSettings, Master, setting, isLite);
});
app.page.sampleFile2sMap = <?php echo isset($view->sampleFile2sMap) ? json_encode($view->sampleFile2sMap): 'null'?>;
app.page.siteMapData = <?php echo isset($view->siteMapData) ? json_encode($view->siteMapData): 'null'?>;
app.page.estateSiteMapData = <?php echo isset($view->estateSiteMapData) ? json_encode($view->estateSiteMapData): 'null'?>;
app.page.siteMapIndexData = <?php echo isset($view->siteMapIndexData) ? json_encode($view->siteMapIndexData): 'null'?>;
app.page.articleCategories = <?php echo isset($view->articleCategories) ? json_encode($view->articleCategories): 'null'?>;
app.page.sampleArticle = <?php echo isset($view->templateArticle) ? json_encode($view->templateArticle): 'null'?>;
</script>
@endsection
@push('style')
<style type="text/css">
/* google map */
div.map-area {
height: 490px;
width: 100%;
}

div.map-area img {
max-width: none;
}

/* keyword error */
.hide-multi-error p:not(:first-child) {
display: none;
}

/* disabled link */
.page-element .page-element-body .item-list input:disabled,
.page-element .page-element-body .item-list select:disabled,
.page-element .page-element-body .item-list-articles input:disabled {
background-color: #E5E5E5;
}

/* ckeditor */
.cke {
	border: 0 none !important;
	background: none !important;
}
.cke_bottom, .cke_top {
	background: none !important;
}
.cke_combo__fontsize {
	width	: 12em		!important	;
}
.cke_combo_text {
	width	:  7em		!important	;
}
</style>
@endpush

@section('content')
<?php if ($view->isTopOriginal): ?>
<script type="text/javascript">
    <?php if (get_class($view->page) ==  'Library\Custom\Hp\Page\Top'): ?>
    app.notify('/data-link/api-get-update-page-parts');
    <?php elseif (get_class($view->page) == 'Library\Custom\Hp\Page\InfoDetail'): ?>
    app.notify('/data-link/api-get-update-notification');
    <?php endif?>
</script>
<?php endif ?>

<?php if ($view->isTopPage): ?>
<script type="text/javascript">
var is_top_page = '1';
</script>
<?php endif ?>

<?php if ($view->page->isPublic() && $view->all_upload_flg && !$view->hasParent) : ?>
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
<?php endif ?>

<?php if(!($isInitialize = isCurrent(null, 'initialize'))):?>
<div id="page-edit">
<?php endif;?>

	<div class="main-contents">
        
        <?php if(!$isInitialize):?>
        <h1><?php echo h($view->pageTitle) ?></h1>

        <?php if ($view->page->isPublic() && $view->all_upload_flg) : ?>
        <div style="padding:5px 30px">
            <div class="alert-strong">
                <p>現在共通設定が未反映の状態です。このページを修正・保存した場合、次回サイトの更新時に自動的に修正内容が反映されます。</p>
                <p>修正内容を即時反映させたくない場合は、先にサイトの公開/更新画面で公開処理を行ってから、編集を行ってください。</p>
            </div>
        </div>
        <?php endif;?>

        <?php endif;?>
        
        <?php $id = h(isset($view->params['id']) ? $view->params['id'] : '')?>
        <?php $parent_id = h(isset($view->params['parent_id']) ? $view->params['parent_id'] : '')?>
        <?php $type = h(isset($view->params['type']) ? $view->params['type'] : '')?>
        <?php if(!$isInitialize):?>
            <?php $form_action = '/page/api-save?id='.$id.'&parent_id='.$parent_id.($view->page->notIsPageInfoDetail() ? '&type='. \Library\Custom\Model\Lists\InfoDatailType::ONLY_ADD_LIST : '');?>
        <?php else:?>
        	<?php
            $targetStatus = 'index';
            switch (getActionName()) {
                case 'index':
                    $targetStatus = 'index';
                    break;
                case 'design':
                    $targetStatus = 'design';
                    break;
                case 'topPage':
                    $targetStatus = 'top-page';
                    break;
                case 'companyProfile':
                    $targetStatus = 'company-profile';
                    break;
                case 'privacyPolicy':
                    $targetStatus = 'privacy-policy';
                    break;
                case 'sitePolicy':
                    $targetStatus = 'site-policy';
                    break;
                case 'contact':
                    $targetStatus = 'contact';
                    break;
                case 'complete':
                    $targetStatus = 'complete';
                    break;
            }
            $form_action = route('api-save-'. $targetStatus)?>
        	<?php $id = $view->page->getId()?>
            <?php $parent_id = ''?>
        <?php endif;?>
        <form data-api-action="<?php echo $form_action?>" data-id="<?php echo $id?>" data-parent-id="<?php echo $parent_id?>" method="post" <?php echo ($view->page->notIsPageInfoDetail() ? 'data-type='.Library\Custom\Model\Lists\InfoDatailType::ONLY_ADD_LIST : '')?> name="pageform">

            <?php // $view->csrfToken() ?>
            @csrf
            <div class="main-contents-body">
            
            <?php if ($isInitialize):?>
            @include('initialize._step')
            <?php endif;?>
            
            
            <?php
            	// $view->page->form->setBelongToRecursive();
            	$subForms = $view->page->form->getSubForms();
                $topPage = get_class($view->page) == "Library\Custom\Hp\Page\Top";
                $list = array("main", "side");
            ?>
            <?php if (getInstanceUser('cms')->checkHasTopOriginal() && $topPage) :?>
                <?php foreach ($subForms as $name => $form):?>
                    <?php if (in_array($name, $list)) :?>
                        @include($form->getTemplate(), [
                                'element' => $form,
                                'page' => $view->page,
                                'isSeo' => $view->isSeo
                        ])
                    <?php endif; ?>
                <?php endforeach;?>
            <?php else :?>
                <?php foreach ($subForms as $name => $form):?>
                    <?php
                        if($name === 'tdk'){ ?>
                            @include($form->getTemplate(), [
				                'element' => $form,
				                'page' => $view->page,
				                'disableTitle' => $view->disableTitle,
				                'isSeo' => $view->isSeo
				            ])
                        <?php }
                        elseif (!$view->page->notIsPageInfoDetail()) { ?>
                            @include($form->getTemplate(), [
				                'element' => $form,
				                'page' => $view->page,
				                'isSeo' => $view->isSeo
				            ])
                        <?php }
                    ?>
                <?php endforeach;?>
            <?php endif; ?>


            </div>
            
			<?php if($isInitialize):?>
			<div class="show-sample">
				<span>プレビュー</span>
			
				<a href="javascript:;" class="btn-t-gray btn-preview" data-type="pc">PCサイトプレビュー</a>
				<a href="javascript:;" class="btn-t-gray btn-preview" data-type="sp">スマホサイトプレビュー</a>
			</div>
			<div class="btns">
				<?php 
					$back = getInstanceUser('cms')->isNerfedTop() ? 'index' : 'design';
					$seq = array('topPage', 'companyProfile', 'privacyPolicy', 'sitePolicy', 'contact', 'privacyPolicy');
					foreach ($seq as $_pageName) {
						if (getActionName() === $_pageName) {
							break;
						}
						$back = $_pageName;
					}
                    switch ($back) {
                        case 'topPage':
                            $back = 'top-page';
                            break;
                        case 'companyProfile':
                            $back = 'company-profile';
                            break;
                        case 'privacyPolicy':
                            $back = 'privacy-policy';
                            break;
                        case 'sitePolicy':
                            $back = 'site-policy';
                            break;
                    }
				?>
				<a class="btn-t-blue size-l" id="back" href="javascript:void(0)" data-link="<?php echo route('default.initialize.'.$back)?>">戻る</a>
				<input type="submit" value="保存して次へ" class="btn-t-blue size-l">
			</div>
			<?php endif;?>
            
        </form>
    </div>

    <?php if(!$isInitialize):?>
    <div id="page-edit-side">
        <ul class="inner">
            <li class="page-edit-status">
                <h3>現在の状態</h3>
                <span class="<?php if(!$view->page->isPublic()):?>is-draft<?php endif;?>"><?php if ($view->page->isPublic()) : ?>公開中<?php else : ?>下書き<?php endif; ?></span>
            </li>
            <li class="page-edit-preview">
                <h3>プレビュー</h3>
                <?php if (!$view->page->notIsPageInfoDetail()) :?>
                <div class="btns">
                    <a href="javascript:;" class="btn-p-pc btn-preview" data-type="pc"></a>
                    <a href="javascript:;" class="btn-p-sp btn-preview" data-type="sp"></a>
                </div>
                <?php else :?>
                <?php echo $view->toolTip('preview-detail_add_list')?>
                <?php endif; ?>
            </li>
            <li class="page-edit-save">
                <a href="javascript:void(0)" class="btn-t-blue">保存</a>
            </li>
            <li class="page-delete-btn <?php if (!$view->page->getId()):?>is-hide<?php endif;?>">
                <?php
                $delRestrictClass = "";
				$classArticle = '';
				if ($view->page->isArticlePage()) {
					$classArticle = ' is-page-delete-article';
				}
                if(!$view->page->canDelete() && $view->page->isPublic()) {
                    $delRestrictClass = 'is-ban-page-delete';
                } else if($view->page->getId() && $view->page->isScheduled()) {
                    $delRestrictClass = 'is-ban-sched-page-delete';
                }
                ?>
                <a href="javascript:void(0)" class="btn-t-gray<?php echo $classArticle;?> <?php if (!empty($delRestrictClass)) { echo $delRestrictClass; }?>">削除</a>
            </li>
        </ul>
    </div>
	<?php endif;?>
    
<?php if(!$isInitialize):?>
</div>
<?php endif;?>

<div id="templates" class="is-hide">

	<div class="select-element">
		<h3>要素挿入<?php echo $view->toolTip('page_insert_parts')?></h3>
		<div class="select-element-body">
			<select>
				<option value="element1">選択してください</option>
			</select>
			<div class="btn-area">
				<a class="btn-t-blue size-s" href="javascript:;">追加</a>
			</div>
		</div>
	</div>
	
	<div class="page-area sortable-item">
		<input type="hidden" class="column-type-code">
		<input type="hidden" class="sort-value">
		<div class="errors is-hide"></div>
	</div>
	
	<div class="col-action">
		<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
		<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
		<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
	</div>
	
	<div id="main-parts">
		<?php foreach ($view->createableMainParts as $name => $parts):?>
        <?php
        if (getInstanceUser('cms')->checkHasTopOriginal() && $topPage) {
            $part = array("Library\Custom\Hp\Page\Parts\EstateKoma", "Library\Custom\Hp\Page\Parts\InfoList");
            if (!in_array(get_class($parts), $part)) { ?>
                @include($parts->getTemplate(), [
                    'element' => $parts,
                    'isTemplate' => true
                ])
            <?php }
        } else {?>
            @include($parts->getTemplate(), [
                    'element' => $parts,
                    'isTemplate' => true
                ])
        <?php } ?>
		<?php endforeach;?>
	</div>

	<div id="side-parts">
		<?php foreach ($view->createableSideParts as $name => $parts):?>
            @include($parts->getTemplate(), [
                'element' => $parts,
                'isTemplate' => true
            ])
		<?php endforeach;?>
	</div>
    
    <?php if (isset($view->terminologyForm)):?>
    <div id="terminologyModal">
        <div class="sample-select">
            <?php $samples = $view->terminologyForm->getSamples();?>
            <select class="terminology-sample" name="">
                <option value="">雛形選択</option>
                <?php foreach ($samples as $idx => $sample):?>
                <option value="<?php echo $idx?>" data-kana="<?php echo $sample['kana']?>" data-content="<?php echo h($sample['content'])?>"><?php echo $sample['title']?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="item-set js-scroll-container" data-scroll-container-max-height="500" style="overflow-y:auto;">
            <form data-api-action="/page/api-validate-terminology">
            <dl class="item-set-header is-require">
                <dt>
                    <span>用語</span>
                </dt>
                <dd>
                    <?php $view->terminologyForm->form('word')?>
                    <span class="input-count">0</span>
                    <div class="errors"></div>
                </dd>
            </dl>
            <div class="item-set-list">
                <dl class="is-require">
                    <dt>
                        <span>読み(ひらがな)</span>
                    </dt>
                    <dd>
                        <?php $view->terminologyForm->form('kana')?>
                        <span class="input-count">0</span>
                        <div class="errors"></div>
                    </dd>
                </dl>
                <dl>
                    <dt>
                        <span>内容</span>
                    </dt>
                    <dd class="element-text-utilcontainer element-text">
                        <div class="mb20">
                        <?php $view->terminologyForm->form('description')?>
                        <span class="input-count">0</span>
                        <div class="errors"></div>
                        </div>
                        <?php //echo $view->partial('_forms/hp-page/parts/partials/text-util.phtml')?>
                        @include('_forms.hp-page.parts.partials.text-util')
                    </dd>
                </dl>
                <dl>
                    <dt>
                        <span>画像</span>
                    </dt>
                    <dd>
                        <div class="select-image">
                            <a href="javascript:void(0);">
                                <span>画像の追加</span>
                            </a>
                            <?php $view->terminologyForm->form('image')?>
                            <div class="errors"></div>
                            <div class="is-require select-image-title">
                                <label>画像タイトル<i class="i-l-require">必須</i></label>
                                <?php $view->terminologyForm->form('image_title')?><span class="input-count">0</span>
                                <div class="errors"></div>
                            </div>
                        </div>
                        <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
                    </dd>
                </dl>
            </div>
            </form>
        </div>
    </div>
    <?php endif;?>
    
    <input type="hidden" name="displayFreeword" id="displayFreeword" value="<?php echo $view->displayFreeword; ?>">
</div>
<?php if ($view->hasSearchSetting): ?>
<div id="template_modal" style="display: none">
    <h2 class="individual-title">種目を選択してください。</h2>
    <div id="enabled_estate_type">
        <?php $baseEstateTypes = $view->form->getElement('enabled_estate_type')->getvalueOptions();
            $name = 'estate_class';
            $estateClassRadios = explode('<br>', trim($view->form->form($name, false)));
        ?>
        <ul class="is-required">
            <?php $i = 0;?>
            <?php foreach ($view->form->getElement($name)->getvalueOptions() as $estateClass => $estateClassLabel):?>
                <li class="<?php if($i != 0):?>mt10<?php endif;?>">
                    <?php echo $estateClassRadios[$i++]?>
                    <ul class="ml20">
                        <?php foreach (\Library\Custom\Model\Estate\TypeList::getInstance()->getByClass($estateClass) as $estateType => $estateTypeName):?>
                            <?php if (!isset($baseEstateTypes[ $estateType ])) continue?>
                            <li style="display: inline-block">
                                <label>
                                    <input data-estate-class="<?php echo $estateClass?>" type="checkbox" name="enabled_estate_type[]" value="<?php echo $estateType?>">
                                    <?php echo h($estateTypeName)?>
                                </label>
                                <?php if(isset($view->shumokuTypeMaster[ $estateType ])) {?>
                                    <a style="margin-left:-43px; margin-right:10px;">詳細な種目を選ぶ</a>
                                <?php }?>
                                <?php if(isset($view->shumokuTypeMaster[ $estateType ])) {?>
                                <div class="shumoku_shosai_box" style="nowrap">
                                    <?php
                                    $cnt = 0;
                                    foreach($view->shumokuTypeMaster[ $estateType ] as $item) {
                                    ?>

                                    <?php if(gettype($item) == 'string') {
                                        echo $item;
                                    } else { ?>
                                    <label style="display: block;float: left;">
                                        <input class="shumoku_shosai" type="checkbox" value="<?php echo $item['item_id'];?>"<?php print ' initialck="'.$item['checked'].'"';?> label_val="<?php echo $item['label'];?>">
                                        <?php echo $item['label'];?>
                                    </label>
                                    <?php } ?>
                                    <?php } ?>
                                    <?php if($estateType == 12) {?>
                                    <br style="clear:both;"/>
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

                                <?php }?>
                            </li>
                        <?php endforeach;?>
                    </ul>
                </li>
            <?php endforeach;?>
        </ul>
        <div class="errors"></div>
    </div>
    <h2 class="individual-title">設定方法を選択してください。</h2>
    <div class="js-method-search">
        <ul class="is-required">
            <?php foreach ($view->searchTypeConditionMaster as $key=>$searchType) :?>
            <li><label><input type="radio" name="search_type[]" value="<?php echo $key;?>"><?php echo $searchType;?></label></li>
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
                <?php $checks = explode('<br>', $view->formMethod->form('publish_estate', false));
                ?>
                <li>
                    <?php echo $checks[0]?>
                </li>
                <li>
                    <?php echo preg_replace('/\<\/label\>$/', '<span class="fs-small" style="display:inline;">※ATBBの物件情報入手にて「取込み」し公開した物件</span></label>', $checks[1])?>
                </li>
                <?php $isAllowedSecondEstate = getInstanceUser('cms')->isAvailableSecondEstate() && $view->acl()->isAllowed('index', 'second-estate-search-setting')?>
                <?php if($isAllowedSecondEstate):?>
                <li>
                    <?php echo $checks[2]?>
                </li>
                <?php endif; ?>
            </ul>
            <p class="list-heading">公開する物件の絞り込みオプション 
                <?php if($isAllowedSecondEstate):?>
                <br>
                <span class="fs-small">※2次広告自動公開の物件が選択されている場合はこのオプションは利用できません。</span>
                <?php endif; ?>
            </p>
            <ul class="list-radio-block">
                <li><?php echo $checks[3]?></li>
            </ul>
            <div class="errors ml0"></div>
        </div>
    </div>
    <h2 class="individual-title">手数料/広告費を選択してください。</h2>
    <div class="tesuryo_kokokuhi">
        <?php $name = 'tesuryo_kokokuhi'?>
        <?php $checks = explode('<br>', $view->formMethod->form($name, false))?>
        <div>
        <?php echo $checks[0]?>
        </div>

        <div class="sp-basic-tesuryo">
            <label><input type="checkbox" id="tesuryo_check"/>手数料ありの物件だけ表示する</label>
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
        <?php echo $checks[3]?>
        </div>
        <div class="errors"></div>
    </div>
</div>
<?php endif ?>
@endsection
