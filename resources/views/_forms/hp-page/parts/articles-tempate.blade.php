<?php
use Library\Custom\Hp\Page\Parts\Element;
use Illuminate\Support\Facades\App;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
?>
<script type="text/javascript">
$(function () {
  'use strict';
  app.page.selectData = <?php echo json_encode($element->simpleSelectData('link_page_id'));?>;
  app.page.ToolTipTitle = <?php echo json_encode($view->toolTip('page_list_title'));?>;
  app.page.ToolTipUpdateDate = <?php echo json_encode($view->toolTip('page_list_update_date'));?>;
  app.page.ToolTipSearchSpecialLabel = <?php echo json_encode($view->toolTip('search_special_label'));?>;
	app.page.initUseLinkLoad($('.item-list-articles.input-img-link'));
});
</script>

<div class="page-element sortable-item element-articles" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
	@include('_forms/hp-page/parts/partials/header-link-auto', array('element' => $element))
	<div class="page-element-body sortable-item-container">
		@include('_forms/hp-page/parts/partials/heading', array('element' => $element))
		<div class="btn-right">
			<a href="javascript:void(0)" class="model_sample_article" data-type="<?php echo $element->getType()?>" data-page-type="">雛形選択</a>
		</div>
		<dl class="item-header">
			<dt><span>画像<i class="i-l-require">必須</i></span></dt>
			<dd>
				<div class="select-image">
					<a href="javascript:;">
					
						<?php if($imageId = $element->getElement('image')->getValue()):?>
						<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
						<?php else:?>
						<span>画像の追加</span>
						<?php endif;?>
					</a>
					<?php $element->simpleHidden('image') ?>
					<?php if($imageId = $element->getElement('image')->getValue()):?>
	                <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
	                <?php else:?>
	                <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
	                <?php endif;?>
					<div class="is-require select-image-title">
						<label>画像タイトル<i class="i-l-require">必須</i></label>
						<?php $element->simpleText('image_title')?><span class="input-count"></span>
						<div class="errors"></div>
					</div>
				</div>
			</dd>
		</dl>

		<dl class="item-header">
			<dt>
				<span>説明文<i class="i-l-require">必須</i></span>
			</dt>
			<dd class="element-text-utilcontainer element-text">
				<div class="mb20">
				<?php $element->simpleText('description')?><span class="input-count"></span>
				<div class="errors"></div>
				</div>
				
				@include('_forms/hp-page/parts/partials/text-util')
			</dd>
		</dl>
		<?php $subForms = $element->getSubForm('elements')->getSubForms()?>
		<?php foreach($subForms as $key=>$form):?>
			<?php
				foreach ($form->getElements() as $name => $ele) {
					$ele->setBelongsTo('', true);
        		}
        	?>
			
		<?php if (empty($element->getElement('image')->getValue())): ?>
		<?php
			//for ($i=0; $i < 3; $i++) { ?>
			<div class="item-set sortable-item added-item sub-parts" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType() ?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()+ $key?>" data-template="<?php echo $form->getTypeArcticle(); ?>">
			<?php $form->simpleHidden('type')?>
			<?php $form->simpleHidden('sort')?>
			
				<dl class="item-set-header is-require">
					<dt><span>項目</span></dt>
					<dd>
						<?php echo $form->simpleText('article_elem_title')?><span class="input-count"></span>
						<div class="errors"></div>
					</dd>
					<dd class="action">
						<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
						<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
						<a class="i-e-delete delete-btn" href="javascript:void(0);">削除</a>
					</dd>
				</dl>
				<div class="item-set-list2 sub-elements sortable-item-container" data-name="<?php echo $form->getName()+ $key?>">
				<?php $subElements = $form->getSubForm('elements')->getSubForms()?>
				@foreach($subElements as $key2 => $element)
					<?php
						foreach ($element->getElements() as $name => $elem) {
							$elem->setBelongsTo('', true);
		        		}
		        	?>
					<?php if ($element instanceof Element\ArticlesText) : ?>
					<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $element->getType()?>" data-is-preset="<?php echo $element->isPreset()?>" data-title="<?php echo $element->getTitle()?>" data-is-unique="<?php echo $element->isUnique()?>">
						<?php $element->simpleHidden('type');?>
						<?php $element->simpleHidden('sort');?>
						<dl>
							<dt>テキスト</dt>
							<dd class="element-text-utilcontainer element-text">
								<div class="mb20">
								<?php $element->simpleText('description')?><span class="input-count"></span>
								<div class="errors"></div>
								</div>
								@include('_forms/hp-page/parts/partials/text-util')
							</dd>
							<dd class="action">
								<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
								<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
								<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
							</dd>
						</dl>
						</div>
					<?php endif ?>
					<?php if ($element instanceof Element\ArticlesImageText) : ?>
					
					<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $element->getType()?>" data-is-preset="<?php echo $element->isPreset()?>" data-title="<?php echo $element->getTitle()?>" data-is-unique="<?php echo $element->isUnique()?>">
						<?php $element->simpleHidden('type');?>
						<?php $element->simpleHidden('sort');?>
						<dl>
							<dt><span>画像</span></dt>
							<dd>
								<div class="select-image">
									<a href="javascript:;">
										<?php if($imageId = $element->getElement('image')->getValue()):?>
										<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
										<?php else:?>
										<span>画像の追加</span>
										<?php endif;?>
									</a>
									<?php $element->simpleHidden('image') ?>

									<?php if($imageId =$element->getElement('image')->getValue()):?>
									<p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
									<?php else:?>
									<p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
									<?php endif;?>
								</div>
								<div class="item-list-articles">
									<div class="is-require input-img-title">
										<label><?php echo $element->getElement('image_title')->getLabel()?><i class="i-l-require">必須</i></label>
										<div class="input-img-wrap">
											<?php $element->simpleText('image_title')?><span class="input-count"></span>
										</div>
										<div class="errors"></div>
									</div>
									<div class="page-element-body" style="padding: 10px 0 0 0;background-color: #fff;">
											<div class="item-list" style="margin: 0;">
											<div class="input-img-link">
												<label><?php $element->form('use_image_link')?><?php echo $element->getElement('use_image_link')->getLabel()?></label>
												<div class="input-img-wrap" style="display:none;">
													<?php $radios = explode("\n", trim($element->form('art_link_type', false)))?>
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
																	
																	<?php $file2 = (App::make(HpFile2RepositoryInterface::class))->fetchFile2Information( $file2Id ) ;?>
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
																				<?php $radiosSearchHouseType = explode("<br />", trim($element->form('search_type', false)))?>
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
							</dd>
							<dd class="action">
								<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
								<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
								<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
							</dd>
						</dl>
						<dl>
							<dt><span>テキスト</span></dt>
							<dd class="element-text-utilcontainer element-text">
								<div class="mb20 image-text">
								<?php $element->simpleText('description')?><span class="input-count"></span>
								<div class="errors"></div>
								</div>
								@include('_forms/hp-page/parts/partials/text-util')
							</dd>
						</dl>
						</div>
					<?php endif ?>
					<?php if ($element instanceof Element\ArticlesImage) : ?>
					<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $element->getType()?>" data-is-preset="<?php echo $element->isPreset()?>" data-title="<?php echo $element->getTitle()?>" data-is-unique="<?php echo $element->isUnique()?>">
						<?php $element->simpleHidden('type');?>
						<?php $element->simpleHidden('sort');?>
						<dl>
							<dt><span>画像</span></dt>
							<dd>
								<div class="select-image">
									<a href="javascript:;">
										<?php if($imageId = $element->getElement('image')->getValue()):?>
										<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
										<?php else:?>
										<span>画像の追加</span>
										<?php endif;?>
									</a>
									<?php $element->simpleHidden('image') ?>

									<?php if($imageId =  $element->getElement('image')->getValue()):?>
									<p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
									<?php else:?>
									<p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
									<?php endif;?>
									<div class="errors"></div>
								</div>
								<div class="item-list-articles">
									<div class="is-require input-img-title">
										<label><?php echo $element->getElement('image_title')->getLabel()?><i class="i-l-require">必須</i></label>
										<div class="input-img-wrap">
											<?php $element->simpleText('image_title')?><span class="input-count"></span>
										</div>
										<div class="errors"></div>
									</div>
									<div class="page-element-body" style="padding: 10px 0 0 0;background-color: #fff;">
										<div class="item-list" style="margin: 0;">
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
																	<?php $file2 = App::make(HpFile2RepositoryInterface::class)->fetchFile2Information( $file2Id ) ;?>
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
																				<?php $radiosSearchHouseType = explode("<br />", trim($element->form('search_type', false)))?>
																				
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
							</dd>
							<dd class="action">
								<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
								<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
								<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
							</dd>
						</dl>
						</div>
					<?php endif ?>
				@endforeach
				<div class="item-add">
					<select></select>
					<a href="javascript:void(0);" class="btn-t-blue size-s">追加</a>
				</div>
			</div>
			</div>
		<?php //}?>
		<?php else: ?>

			<div class="item-set sortable-item added-item sub-parts" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
			<?php $form->simpleHidden('type')?>
			<?php $form->simpleHidden('sort')?>
			<dl class="item-set-header is-require">
				<dt><span>項目</span></dt>
				<dd>
					<?php echo $form->simpleText('article_elem_title')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
				<dd class="action">
					<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
					<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
					<a class="i-e-delete delete-btn" href="javascript:void(0);">削除</a>
				</dd>
			</dl>
			<div class="item-set-list2 sub-elements sortable-item-container" data-name="<?php echo $form->getName()?>">
				<?php $subElements = $form->getSubForm('elements')->getSubForms() ?>
				<?php foreach ($subElements as $key2 => $elem):?>
					<?php
						foreach ($elem->getElements() as $name => $ele) {
							$ele->setBelongsTo('', true);
						}
					?>
					<?php if ($elem instanceof Element\ArticlesText) : ?>
					<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $elem->getType()?>" data-is-preset="<?php echo $elem->isPreset()?>" data-title="<?php echo $elem->getTitle()?>" data-is-unique="<?php echo $elem->isUnique()?>">
						<?php $elem->simpleHidden('type');?>
						<?php $elem->simpleHidden('sort');?>
						<dl>
							<dt>テキスト</dt>
							<dd class="element-text-utilcontainer element-text">
								<div class="mb20">
								<?php $elem->simpleText('description')?><span class="input-count"></span>
								<div class="errors"></div>
								</div>
								@include('_forms/hp-page/parts/partials/text-util')
							</dd>
							<dd class="action">
								<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
								<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
								<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
							</dd>
						</dl>
					</div>	
					<?php endif ?>
					<?php if ($elem instanceof Element\ArticlesImageText) : ?>
					<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $elem->getType()?>" data-is-preset="<?php echo $elem->isPreset()?>" data-title="<?php echo $elem->getTitle()?>" data-is-unique="<?php echo $elem->isUnique()?>">
						<?php $elem->simpleHidden('type');?>
						<?php $elem->simpleHidden('sort');?>
						<dl>
							<dt><span>画像</span></dt>
							<dd>
								<div class="select-image">
									<a href="javascript:;">
										<?php if($imageId = $elem->getElement('image')->getValue()):?>
											<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
										<?php else:?>
											<span>画像の追加</span>
										<?php endif;?>
									</a>
									<?php $elem->simpleHidden('image') ?>

									<?php if($imageId = $elem->getElement('image')->getValue()):?>
										<p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
									<?php else:?>
										<p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
									<?php endif;?>
								</div>
								<div class="item-list-articles">
									<div class="is-require input-img-title">
										<label><?php echo $elem->getElement('image_title')->getLabel()?><i class="i-l-require">必須</i></label>
										<div class="input-img-wrap">
											<?php $elem->simpleText('image_title')?><span class="input-count"></span>
										</div>
										<div class="errors"></div>
									</div>
									<div class="page-element-body" style="padding: 10px 0 0 0;background-color: #fff;">
										<div class="item-list" style="margin: 0;">
											<div class="input-img-link">
												<?php ?>
												<label><?php $elem->form('use_image_link')?><?php echo $elem->getElement('use_image_link')->getLabel()?></label>
												<div class="input-img-wrap" style="display:none;">
													<?php $radios = explode("\n", trim($elem->form('art_link_type', false)))?>
													<label><?php $elem->form('link_target_blank')?><?php echo $elem->getElement('link_target_blank')->getLabel()?></label>
													<div class="search-btn link-wrapper">
															<label class="select-page-radio">
																<?php echo $radios[0]?>
																<a class="btn-t-gray" href="javascript:;">ページを検索</a>
															</label>
															<ul>
																	<li class="page-name">
																			<?php echo $elem->getSelectPageTitle('link_page_id'); ?>
																	</li>
																	<div class="is-hide select-page"><?php $elem->simpleSelect('link_page_id')?></div>
															</ul>
															<div class="errors"></div>
													</div>
													<dl class="link-wrapper">
														<dt><?php echo $radios[1]?></dt>
														<dd>
															<?php $elem->simpleText('link_url')?><span class="input-count link-url-count"></span>
															<div class="errors"></div>
														</dd>
													</dl>
													<dl class="link-wrapper">
														<div>
															<dt><?php echo $radios[2] ?></dt>
															<dd>
																<div class="select-file2">
																	<?php if( $file2Id = $elem->getElement('file2')->getValue() ):?>
																	<?php $file2 = App::make(HpFile2RepositoryInterface::class)->fetchFile2Information( $file2Id ) ;?>
																	<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
																	<p class="select-file2-title">選択中ファイル：<?php  echo $file2['title'].'.'.$file2['extension']?></p>
																	<?php else:?>
																	<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
																	<?php endif;?>
																	<?php $elem->simpleHidden( 'file2' )?>
																</div>
															</dd>
														</div>
														<div class="errors" style="position: relative;left: 23px;display: table-row;white-space: nowrap"></div>
													</dl>
												<?php if ($elem->getElement('link_house')): ?>
												<dl class="link-wrapper">
														<dt><?php echo $radios[3] ?></dt>
														<div class="link-house-module link-house-module-edit">
																<ul>
																		<li class="search-house-method">
																				<?php $radiosSearchHouseType = explode("<br />", trim($elem->form('search_type', false)))?>
																				<?php echo $radiosSearchHouseType[0];?>
																				<?php echo $radiosSearchHouseType[1];?>
																		</li>
																		<li class="content-search-method">
																				<div>
																						<a class="btn-t-gray btn-search-all-house" href="javascript:;">物件を検索</a>
																				</div>
																				<div class="is-hide">
																					<?php $elem->simpleText( 'house_no' )?>
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
																					<?php $elem->simpleHidden( 'link_house' )?>
																				</div>
																		</li>
																		<li class="member-no-info is-hide">
																				<label></label> 
																				<label class="display-house-no"></label>
																				<?php $elem->simpleHidden( 'link_house_type' )?>
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
							</dd>
							<dd class="action">
								<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
								<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
								<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
							</dd>
						</dl>
						<dl>
							<dt><span>テキスト</span></dt>
							<dd class="element-text-utilcontainer element-text">
								<div class="mb20 image-text">
									<?php $elem->simpleText('description')?><span class="input-count"></span>
									<div class="errors"></div>
								</div>
								@include('_forms/hp-page/parts/partials/text-util')
							</dd>
						</dl>
					</div>
					<?php endif ?>
					
					<?php if ($elem instanceof Element\ArticlesImage) : ?>
					<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $elem->getType()?>" data-is-preset="<?php echo $elem->isPreset()?>" data-title="<?php echo $elem->getTitle()?>" data-is-unique="<?php echo $elem->isUnique()?>">
						<?php $elem->simpleHidden('type');?>
						<?php $elem->simpleHidden('sort');?>
						<dl>
							<dt><span>画像</span></dt>
							<dd>
								<div class="select-image">
									<a href="javascript:;">
									
										<?php if($imageId = $elem->getElement('image')->getValue()):?>
											<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
										<?php else:?>
											<span>画像の追加</span>
										<?php endif;?>
									</a>
									<?php $elem->simpleHidden('image') ?>
									<?php if($imageId = $elem->getElement('image')->getValue()):?>
										<p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
									<?php else:?>
										<p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
									<?php endif;?>
									<div class="errors"></div>
								</div>
								<div class="item-list-articles">
									<div class="is-require input-img-title">
										<label><?php echo $elem->getElement('image_title')->getLabel()?><i class="i-l-require">必須</i></label>
										<div class="input-img-wrap">
											<?php $elem->simpleText('image_title')?><span class="input-count"></span>
										</div>
										<div class="errors"></div>
									</div>
									<div class="page-element-body" style="padding: 10px 0 0 0;background-color: #fff;">
										<div class="item-list" style="margin: 0;">
											<div class="input-img-link">
												<label><?php $elem->form('use_image_link')?><?php echo $elem->getElement('use_image_link')->getLabel()?></label>
												<div class="input-img-wrap" style="display:none;">
													<?php $radios = explode("\n", trim($elem->form('link_type', false)))?>
													<label><?php $elem->form('link_target_blank')?><?php echo $elem->getElement('link_target_blank')->getLabel()?></label>
													<div class="search-btn link-wrapper">
															<label class="select-page-radio">
																<?php echo $radios[0]?>
																<a class="btn-t-gray" href="javascript:;">ページを検索</a>
															</label>
															<ul>
																	<li class="page-name">
																			<?php echo $elem->getSelectPageTitle('link_page_id'); ?>
																	</li>
																	<div class="is-hide select-page"><?php $elem->simpleSelect('link_page_id')?></div>
															</ul>
															
															<div class="errors"></div>
													</div>
													<dl class="link-wrapper">
														<dt><?php echo $radios[1]?></dt>
														<dd>
															<?php $elem->simpleText('link_url')?><span class="input-count link-url-count"></span>
															<div class="errors"></div>
														</dd>
													</dl>
													<dl class="link-wrapper">
														<div>
															<dt><?php echo $radios[2] ?></dt>
															<dd>
																<div class="select-file2">
																	<?php if( $file2Id = $elem->getElement('file2')->getValue() ):?>
																	<?php $file2 = App::make(HpFile2RepositoryInterface::class)->fetchFile2Information( $file2Id ) ;?>
																	
																	<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
																	<p class="select-file2-title">選択中ファイル：<?php echo $file2['title'].'.'.$file2['extension']?></p>
																	<?php else:?>
																	<a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
																	<?php endif;?>
																	<?php $elem->simpleHidden( 'file2' )?>
																</div>
															</dd>
														</div>
														<div class="errors" style="position: relative;left: 23px;display: table-row;white-space: nowrap"></div>
													</dl>
													<?php if ($elem->getElement('link_house')): ?>
													<dl class="link-wrapper">
															<dt><?php echo $radios[3] ?></dt>
															<div class="link-house-module link-house-module-edit">
																	<ul>
																			<li class="search-house-method">
																					<?php $radiosSearchHouseType = explode("<br />", trim($elem->form('search_type', false)))?>
																					<?php echo $radiosSearchHouseType[0];?>
																					<?php echo $radiosSearchHouseType[1];?>
																			</li>
																			<li class="content-search-method">
																					<div>
																							<a class="btn-t-gray btn-search-all-house" href="javascript:;">物件を検索</a>
																					</div>
																					<div class="is-hide">
																						<?php $elem->simpleText( 'house_no' )?>
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
																						<?php $elem->simpleHidden( 'link_house' )?>
																					</div>
																			</li>
																			<li class="member-no-info is-hide">
																					<label></label> 
																					<label class="display-house-no"></label>
																					<?php $elem->simpleHidden( 'link_house_type' )?>
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
							</dd>
							<dd class="action">
								<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
								<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
								<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
							</dd>
						</dl>
					</div>
					<?php endif ?>
				<?php endforeach ?>
				<div class="item-add">
				    <select></select>
				    <a href="javascript:void(0);" class="btn-t-blue size-s">追加</a>
			    </div>
			</div>
		</div>
		
		<?php endif; ?>
		<?php endforeach; ?>
		<div class="item-add">
			<a href="javascript:void(0);" class="btn-t-blue size-s">項目を追加</a>
		</div>
	</div>
</div>
