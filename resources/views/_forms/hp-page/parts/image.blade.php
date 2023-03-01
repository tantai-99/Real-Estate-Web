@push('script')
<script type="text/javascript">
$(function () {
  'use strict';
  app.page.selectData = <?php echo json_encode($element->simpleSelectData('link_page_id'));?>;
  app.page.ToolTipTitle = <?php echo json_encode($view->toolTip('page_list_title'));?>;
  app.page.ToolTipUpdateDate = <?php echo json_encode($view->toolTip('page_list_update_date'));?>;
  app.page.ToolTipSearchSpecialLabel = <?php echo json_encode($view->toolTip('search_special_label'));?>;
	//app.page.initUseLinkLoad($('.main-contents-image .input-img-link'));
});
</script>
@endpush
<div class="page-element sortable-item element-image main-contents-image" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">

	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body">
	
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<div class="select-image">
			<a href="javascript:void(0);">
				<?php if($imageId = $element->getElement('image')->getValue()):?>
				<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
				<?php else:?>
				<span>画像の追加</span>
				<?php endif;?>
			</a>
			<?php $element->simpleHidden('image')?>

			<?php if($imageId = $element->getElement('image')->getValue()):?>
            <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
            <?php else:?>
            <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
            <?php endif;?>

			<div class="errors"></div>
		</div>
		
		<div class="item-list">
			<div class="is-require input-img-title">
				<label><?php echo $element->getElement('image_title')->getLabel()?><i class="i-l-require">必須</i></label>
				<div class="input-img-wrap mb20">
					<?php $element->simpleText('image_title')?><span class="input-count"></span>
				</div>
				<div class="errors"></div>
			</div>

			<div class="input-img-link">
				<label><?php $element->form('use_image_link')?><?php echo $element->getElement('use_image_link')->getLabel()?></label>
				<div class="input-img-wrap" style="display:none;">
					<?php $radios = explode("\n", trim($element->form('link_type', false)))?>
					<label><?php $element->form('link_target_blank')?><?php echo $element->getElement('link_target_blank')->getLabel()?></label>
					<div class="search-btn link-wrapper">
						<label class="select-page-radio">
							<?php echo $radios[0]?>
							<a class="btn-t-gray" href="javascript:;">ページを検索</a>
						</label>
						<ul>
							<li class="page-name">
								<?php echo $element->getSelectPageTitle('link_page_id'); ?>
							</li>
							<div class="is-hide select-page"><?php $element->simpleSelect('link_page_id')?></div>
						</ul>
						<div class="errors"></div>
					</div>
					<dl class="link-wrapper">
						<dt><?php echo $radios[1]?></dt>
						<dd>
							<?php $element->simpleText('link_url')?><span class="input-count link-url-count"></span>
							<div class="errors"></div>
						</dd>
					</dl>
					<dl class="link-wrapper">
						<div>
							<dt><?php echo $radios[2] ?></dt>
							<dd>
								<div class="select-file2">
									<?php if( $file2Id = $element->getElement('file2')->getValue() ):?>
									<?php $file2 = \App::make(\App\Repositories\HpFile2\HpFile2RepositoryInterface::class)->fetchFile2Information( $file2Id ) ;?>
									<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
									<p class="select-file2-title">選択中ファイル：<?php echo $file2['title'].'.'.$file2['extension']?></p>
									<?php else:?>
									<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
									<?php endif;?>
									<?php $element->simpleHidden( 'file2' )?>
								</div>
							</dd>
						</div>
						<div class="errors" style="position: relative;left: 23px;display: table-row;white-space: nowrap"></div>
					</dl>
                    <?php if ($element->getElement('link_house')): ?>
                    <dl class="link-wrapper">
                        <dt><?php echo $radios[3] ?></dt>
						<div class="link-house-module link-house-module-edit">
								<ul>
										<li class="search-house-method">
                                            <?php $radiosSearchHouseType = explode("<br />", trim($element->form('search_type', false)));
                                            ?>
                                            <?php echo $radiosSearchHouseType[0];?>
                                            <?php echo $radiosSearchHouseType[1];?>
										</li>
										<li class="content-search-method">
												<div>
														<a class="btn-t-gray btn-search-all-house" href="javascript:;">物件を検索</a>
												</div>
												<div class="is-hide">
												<?php $element->simpleText( 'house_no' )?>
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
												<?php $element->simpleHidden( 'link_house' )?>
												</div>
										</li>
										<li class="member-no-info is-hide">
												<label></label>
												<label class="display-house-no"></label>
                                                <?php $element->simpleHidden( 'link_house_type' )?>
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

</div>
