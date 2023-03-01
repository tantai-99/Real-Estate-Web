<?php $subForms = $element->getSubForm('elements')->getSubForms(); ?>
@push('script')
<script type="text/javascript">
	$(function () {
	  'use strict';
	  app.page.selectData = <?php echo json_encode($subForms[0]->simpleSelectData('link_page_id'));?>;
	  app.page.ToolTipTitle = <?php echo json_encode($view->toolTip('page_list_title'));?>;
	  app.page.ToolTipUpdateDate = <?php echo json_encode($view->toolTip('page_list_update_date'));?>;
	  app.page.ToolTipSearchSpecialLabel = <?php echo json_encode($view->toolTip('search_special_label'));?>;
	});
</script>
@endpush
<div class="page-element sortable-item element-link side-element-link" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
	@include('_forms.hp-page.side-parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.side-parts.partials.heading', ['element' => $element])

		<?php foreach ($subForms as $key => $form):?>
		<div class="item-list added-item sortable-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
			<?php $form->form('type')?>
			<?php $form->form('sort')?>
			
			<div class="input-img-link">
				<div class="action">
					<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
					<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
					<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
				</div>
				<div class="input-img-wrap">
					<dl>
						<label class="link-target-blank"><?php $form->form('link_target_blank')?><?php echo $form->getElement('link_target_blank')->getLabel()?></label>
					</dl>
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
						<dt class="side-radio"><?php echo $radios[1]?></dt>
						<dd>
							<?php $form->form('link_url')?><span class="input-count"></span>
							<div class="errors"></div>
							<?php $form->form('link_label')?><span class="input-count"></span>
							<div class="errors"></div>
						</dd>
					</dl>
					<dl class="link-wrapper">
						<div class="link-flex-wrapper">
							<dt class="side-radio"><?php echo $radios[2] ?></dt>
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
						<dd>
							<div class="is-require input-img-title">
								<?php $form->form( 'file2_title' )?><span class="input-count"></span>
							</div>
							<div class="errors"></div>
						</dd>
                    </dl>
                    <?php if ($form->getElement('link_house')): ?>
                    <dl class="link-wrapper">
                        <dt class="side-radio"><?php echo $radios[3] ?></dt>
                        <dd>
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
                                        <div class="is-hide">
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
                                    <li class="is-require custom-house-title">
                                        <!-- <label>リンク名</label> --><?php $form->form( 'link_house_title' )?><span class="input-count link_house_title-count"></span>
                                    </li>
									<div class="errors link-house-title-errors"></div>
                                </ul>
                                
                            </div>
                        </dd>
                    </dl>
                    <?php endif;?>
				</div>
			</div>
			
		</div>
		<?php endforeach;?>
		
		<div class="item-add">
			<a class="btn-t-blue size-s" href="javascript:void(0);">リンクを追加</a>
		</div>
		
	</div>
</div>