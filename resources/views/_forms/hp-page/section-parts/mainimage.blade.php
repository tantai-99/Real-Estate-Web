@push('script')
<?php 
    $company    = \App::make(\App\Repositories\Company\CompanyRepositoryInterface::class);
    $companyRow = $company->fetchRowByHpId($element->getPage()->hp_id);

    $advanceSlide = false;
    $checkSlideshow = 'false';
    if ($companyRow && ($companyRow->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE'))) {
        $typeSlideshow = \Library\Custom\Model\Lists\InformationMainImageSlideShow::getNamesEffect();
        $Slideshow = $element->getValue('type_slideshow');
        if ($element->getValue('slide_show_flg') == 1) {
            $checkSlideshow = 'true';   
        }
        $imageSlide = 0;
        $advanceSlide = true;
        echo '<script type="text/javascript" src="/js/page.top.js"></script>';
        $slideForm = $element ;
    }
    $subForms = $element->getSubForms();
?>
<script type="text/javascript">
$(function () {
	'use strict';

	var $mainImageContainer = $('.section-main-image');
	var $selectImage = $mainImageContainer.find('.select-image a');
	var $elements = $mainImageContainer.find('.main-image-element');
	var $images = $mainImageContainer.find('.item-add');
	app.page.selectData = <?php echo json_encode($subForms['main_image[0]']->simpleSelectData('link_page_id'));?>;
	app.page.ToolTipTitle = <?php echo json_encode($view->toolTip('page_list_title'));?>;
	app.page.ToolTipUpdateDate = <?php echo json_encode($view->toolTip('page_list_update_date'));?>;
	app.page.ToolTipSearchSpecialLabel = <?php echo json_encode($view->toolTip('search_special_label'));?>;

	$selectImage.on('app-page-image-selected', function () {

		var $element = $(this).closest('.main-image-element');
		var no = $element.attr('data-no');

		var $image = $images.find('.item-view[data-no="'+ no +'"]');
		$image.find('img').attr('src', $(this).find('img').attr('src'));
		$image.find('.i-e-delete').removeClass('is-disable');
        <?php if ($advanceSlide) : ?>
            app.page.top.addImgSlide($image);
        <?php endif; ?>
	});

	// アクティブ
	$images.on('click', '.item-view-thumb', function () {
		var $this = $(this);
		var $item = $this.closest('.item-view');

		if ($item.hasClass('is-none')) {
			$item = $images.find('.is-none:first');
		}

		if ($item.hasClass('is-active')) {
			return;
		}

		$item.addClass('is-active').siblings().removeClass('is-active');
		$elements.addClass('is-hide').filter('[data-no="'+ $item.attr('data-no') +'"]').removeClass('is-hide');
	});

	// 並び順
	function updateSort () {
		var $children = $images.children();
		$children.each(function (i) {
			var $this = $(this);
			var no = $this.attr('data-no');

			$this.find('.i-e-left').toggleClass('is-disable', i === 0);
			$this.find('.i-e-right').toggleClass('is-disable', i === $children.length - 1);
			$elements.filter('[data-no="' + no + '"]').find('.sort-value').val(i);
		});
        <?php if ($advanceSlide) : ?>
            app.page.top.updateFigcaption(0);
        <?php endif; ?>
	}
	$images.on('click', '.i-e-left:not(.is-disable)', function () {
		var $item = $(this).closest('.item-view');
		$item.after($item.prev());
		updateSort();
	});
	$images.on('click', '.i-e-right:not(.is-disable)', function () {
		var $item = $(this).closest('.item-view');
		$item.before($item.next());
		updateSort();
	});

	// 削除
	$images.on('click', '.i-e-delete:not(.is-disable)', function () {
		var $this = $(this);
		var $item = $this.closest('.item-view');
		var $thumb = $item.find('.item-view-thumb');
		var $element = $elements.filter('[data-no="'+ $item.attr('data-no') +'"]');

		$element.find('.select-image a').html('<span>画像の追加</span>').next().val('');
		$element.find('.select-image .select-image__tx_annotation').html("「画像の追加」をクリックして画像フォルダから追加してください。");

		$element.find('input:text').val('').change();
		$element.find('input:radio:first').prop('checked', true);
		$element.find('input:checkbox').prop('checked', false);
		$element.find('select')[0].selectedIndex = 0;

		$element.find('.input-img-link .input-img-wrap').hide();

		$thumb.find('img').attr('src', $thumb.attr('data-empty-image'));
		$item.find('.i-e-delete').addClass('is-disable');

		app.page.toggleImageTitle($element.find('.input-img-title'), false);
        <?php if ($advanceSlide) : ?>
            app.page.top.removeImgSlide($thumb);
        <?php endif; ?>
	});

	// エラー処理
	$('form').on('app-api-form-error', function () {
		var $firstError = $elements.find('.errors p');
		if (!$firstError.length) {
			return;
		}

		var $element = $firstError.closest('.main-image-element');
		var no = $element.attr('data-no');

		$images.find('.item-view[data-no="'+ no +'"] .item-view-thumb').click();
	});

	// 初期化
	app.page.initUseLinkLoad($elements, true);
	
	updateSort();
    <?php if ($advanceSlide) : ?>
        app.page.top.loadSlide(<?php echo $checkSlideshow ?>);
        app.page.top.load();
    <?php endif; ?>
});
</script>
@endpush

<div class="section section-main-image">
	<h2>メインイメージ<a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.content') ?>', '', 'width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>

	<!-- メインイメージ -->
	<div class="page-area">
		<div class="page-element element-image">
			<div class="page-element-header">
				<h3><span>メインイメージ</span><?php echo $view->toolTip('page_parts_mainimage')?></h3>
			</div>

			<div class="page-element-body">
				<div class="btn-right">
					<a href="<?php echo route('utility.main-image-guideline'); ?>" class="i-s-material" target="_blank">素材集</a>
				</div>

				<?php foreach ($subForms as $no => $form):?>
                <?php $no = str_replace(['main_image[', ']'], '', $no);//$form->setDataCustom(true)?>
				<div class="main-image-element<?php if($no != 0):?> is-hide<?php endif;?>" data-no="<?php echo $no?>">
					<?php echo $form->form('sort')?>
					<div class="main-image select-image">
						<a href="javascript:void(0);">
							<?php if($imageId = $form->getElement('image')->getValue()):?>
							<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
							<?php else:?>
							<span>画像の追加</span>
							<?php endif;?>
						</a>
						<?php $form->form('image')?>
						<div class="errors"></div>

						<?php if($imageId = $form->getElement('image')->getValue()):?>
                        <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
                        <?php else:?>
                        <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
                        <?php endif;?>

						<p class="main-image__tx_annotation">
						表示されるサイズは、縦320×横720ピクセルです。異なるサイズの画像を設定した場合は自動でリサイズされます。
						<br>
						※横幅に合わせてリサイズするため、縦横比が異なる画像の場合は下部分が切れて表示されます。
						<br>
						※デザインが「スタンダード2」の場合、縦320×横980ピクセルで表示されます。
						</p>

					</div>
					<div class="item-list">

						<div class="is-require input-img-title">
							<label>画像タイトル<i class="i-l-require">必須</i></label>
							<div class="input-img-wrap mb20">
								<?php $form->form('image_title')?><span class="input-count"></span>
								<div class="errors"></div>
							</div>
						</div>

						<div class="input-img-link">
							<label><?php $form->form('use_image') ?><?php echo $form->getElement('use_image')->getLabel(); ?></label>
							<div class="input-img-wrap" style="display:none;">
								<label><?php $form->form('link_target_blank')?><?php echo $form->getElement('link_target_blank')->getLabel()?></label>
								<?php $radios = explode("\n", trim($form->form('link_type', false)))?>
								<div class="search-btn link-wrapper">
									<label class="select-page-radio">
										<?php echo $radios[0]?>
										<a class="btn-t-gray" href="javascript:;">ページを検索</a>
									</label>
									<ul>
										<li class="page-name">
											<?php echo $form->getSelectPageTitle('link_page_id'); ?>
										</li>
										<div class="is-hide select-page"><?php $form->form('link_page_id')?></div>
									</ul>
									<div class="errors"></div>
								</div>
								<dl class="link-wrapper">
									<dt><?php echo $radios[1]?></dt>
									<dd>
										<?php $form->form('link_url')?><span class="input-count link-url-count"></span>
										<div class="errors"></div>
									</dd>
								</dl>
								<dl class="link-wrapper">
									<div>
										<dt><?php echo $radios[2] ?></dt>
										<dd>
											<div class="select-file2">
												<?php if( $file2Id = $form->getElement('file2')->getValue() ):?>
												<?php $file2 = \App::make(\App\Repositories\HpFile2\HpFile2RepositoryInterface::class)->fetchFile2Information( $file2Id ) ;?>
												<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
												<p class="select-file2-title">選択中ファイル：<?php echo $file2['title'].'.'.$file2['extension']?></p>
												<?php else:?>
												<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
												<?php endif;?>
												<?php $form->form( 'file2' )?>
											</div>
										</dd>
									</div>
									<div class="errors" style="position: relative;left: 23px;display: table-row;white-space: nowrap"></div>
								</dl>
								<?php  if ($form->getElement('link_house')): ?>
								<dl class="link-wrapper">
									<dt><?php echo $radios[3] ?></dt>
									<div class="link-house-module link-house-module-edit">
										<ul>
											<li class="search-house-method">
                                                <?php $radiosSearchHouseType = explode("<br />", trim($form->form('search_type', false)))?>
												<?php echo $radiosSearchHouseType[0];?>
												<?php echo $radiosSearchHouseType[1];?>
											</li>
											<li class="content-search-method">
												<div>
													<a class="btn-t-gray btn-search-all-house" href="javascript:;">物件を検索</a>
												</div>
												<div>
												<?php $form->form( 'house_no' )?>
												<a class="btn-t-gray btn-search-house-no" href="javascript:;">検索</a>
												</div>
												<div class="error"></div>				
											</li>
											<div class="error"></div>
											<li class="display-house-title">
												<label>選択中の物件<?php echo $view->toolTip('display_house_title')?></label>
												<div class="house-title">
												<label></label>
												<a href="javascript:;" class="btn-p-pc btn-preview-link-house is-hide" data-type="pc"></a>
												<?php $form->form( 'link_house' )?>
												</div>
											</li>
											<li class="member-no-info is-hide">
												<label></label>
												<label class="display-house-no"></label>
                                                <?php $form->form( 'link_house_type' )?>
											</li>
											<div class="errors"></div>
										</ul>
									</div>
                                </dl>
                                <?php endif;?>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach;?>
				<div class="item-add">
					<?php reset($subForms);
					foreach ($subForms as $no => $form) :?>
					<?php $no = str_replace(['main_image[', ']'], '', $no);
						$imageId = $form->getElement('image')->getValue(); ?>
					<div class="item-view<?php if($no == 0):?> is-active<?php endif;?>" data-no="<?php echo $no?>">
						<a href="javascript:;" class="item-view-thumb" data-empty-image="/images/page-edit/image_add.jpg">
							<img src="<?php if($imageId):?>/image/hp-image?image_id=<?php echo $imageId?><?php else:?>/images/page-edit/image_add.jpg<?php endif;?>" alt="">
						</a>
						<div class="item-overlay">
							<a href="javascript:;" class="i-e-left"></a>
							<a href="javascript:;" class="i-e-right"></a>
							<a href="javascript:;" class="i-e-delete<?php if(!$imageId):?> is-disable<?php endif;?>"></a>
						</div>
					</div>
					<?php endforeach;?>
				</div>
				<p class="select-image__tx_annotation">画像を追加・変更する箇所をクリックしてください。</p>
                <?php 
                    if ($advanceSlide) :
                ?>
                <div class="main-image-slideshow">
                    <div id="main_image-slide_show_flg">
                        <div class="errors"></div>
                    </div>
                    <p class="select-image__tx_annotation">スライドショー機能がオフの場合、左端の画像のみ表示されます。</p>
                    <div class="item-list f-switch">
                        <div class="switch-area">
                            <label>スライドショー機能</label>
                            <?php echo $view->toolTip('page_parts_slide_show_title')?>
                            <div class="switch">
                            <?php $name="slide_show_flg"; ?>
                            <?php foreach ($slideForm->getElement('slide_show_flg')->getValueOptions() as $value => $label):?>
                                <input type="radio" id="<?php echo $name?>_<?php echo $value?>" name="main_image[<?php echo $name?>]" value="<?php echo $value?>" class="<?php echo $value?'on':'off'?>"<?php if($value == $slideForm->getElement('slide_show_flg')->getValue()):?> checked="checked"<?php endif;?>>
                            <label for="<?php echo $name?>_<?php echo $value?>"><?php echo h($label)?></label>
                            <?php endforeach;?>
                            </div>
                        </div>
                        <?php $slideForm->form('count_slide');?>
                    </div>
                    <div class="section design-theme" id="slideshow">
                        <div class="flexslider">
                        <?php $name = 'type_slideshow';?>
                            <?php $slideForm->form('type_slideshow');?>
                            <ul class="slides">
                                <li><div style="width: 843px; display: block; margin:0 auto; background: transparent;padding:0px">
                                <?php 
                                $init_effect = 1 ;
                                foreach ($typeSlideshow as $id => $name) {
                                    if ($init_effect%6==1 && $init_effect!=1) { echo '</li><li><div style="width: 843px; display: block; margin:0 auto; background: transparent;padding:0px">'; }
                                    $init_effect++;
                                ?>
                                    <a href="javascript:;" data-id="<?php echo $id; ?>" name="slideshow" class="<?php echo ($Slideshow == $id) ? 'is-active' : ''; ?>" >
                                        <div style="height: 162px; display: block">
											<img src="/images/type-effect/<?php echo \Library\Custom\Model\Lists\InformationMainImageSlideShow::getAminationsGif($id) ?>.gif" />
                                        </div>
                                        <span><?php echo $name; ?></span>
                                    </a>
                                <?php } ?>
                                </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="section" id="tooltip-slideshow">
                        <p class="mb10">その他のオプション</p>
                        <table class="form-basic">
                            <tbody>
                                <tr>
                                    <th><span>スライドの切替時間<a class="tooltip left" href="javascript:;"><i class="i-s-tooltip"></i><div class="tooltip-body">画像をスライドさせるタイミングを設定します。はやめは約3秒、ふつうは約5秒、ゆっくりは約7秒で切り替わります。</div></a></span></th>
                                    <td>
                                        <?php $slideForm->form('time_slideshow');?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span>切替ナビゲーション<a class="tooltip left" href="javascript:;"><i class="i-s-tooltip"></i><div class="tooltip-body"><img src="/images/common/slide_switching_navigation.png"></div></a></span></th>
                                    <td>
                                        <?php $slideForm->form('nav_slideshow');?>
                                        <div class="errors"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span>矢印<a class="tooltip left" href="javascript:;"><i class="i-s-tooltip"></i><div class="tooltip-body"><img src="/images/common/slide_arrow.png"></div></a></span></th>
                                    <td>
                                        <label class="container" for="main_image-arrow_slideshow">
                                            <?php $slideForm->form('arrow_slideshow');?>
                                            <span class="checkmark"></span>
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
			</div>
		</div>

	</div>
</div>
