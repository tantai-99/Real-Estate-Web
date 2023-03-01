<div class="page-element sortable-item element-list" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<?php $subForms = $element->getSubForm('elements')->getSubForms()?>
		<?php foreach ($subForms as $key => $form):?>
		<div class="item-set sortable-item added-item sub-parts" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
			<?php $form->simpleHidden('type')?>
			<?php $form->simpleHidden('sort')?>
			
			<dl class="item-set-header">
				<dt><span>カテゴリ</span></dt>
				<dd>
					<?php echo $form->simpleSelect('category')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
				<dd class="action">
					<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
					<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
					<a class="i-e-delete delete-btn" href="javascript:void(0);">削除</a>
				</dd>
			</dl>
			<div class="item-set-list2 sub-elements sortable-item-container">
				
				<?php $subElements = $form->getSubForm('elements')->getSubForms()?>
				<?php foreach ($subElements as $key2 => $element):?>
				<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $element->getType()?>" data-is-preset="<?php echo $element->isPreset()?>" data-title="<?php echo $element->getTitle()?>" data-is-unique="<?php echo $element->isUnique()?>">
					<?php $element->simpleHidden('type');?>
					<?php $element->simpleHidden('sort');?>
					
					<dl class="item-set-header is-require">
						<dt><span>施設名</span></dt>
						<dd>
							<?php echo $element->simpleText('name')?><span class="input-count"></span>
							<div class="errors"></div>
						</dd>
						<dd class="action">
							<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
							<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
							<a class="i-e-delete delete-btn" href="javascript:void(0);">削除</a>
						</dd>
					</dl>
					<dl>
						<dt>徒歩分数</dt>
						<dd>
							<?php $element->simpleText('walking_minutes')?><span class="input-count"></span>
							<div class="errors"></div>
						</dd>
					</dl>
					<dl>
						<dt>説明</dt>
						<dd class="element-text-utilcontainer element-text">
							<div class="mb20">
							<?php $element->simpleText('description')?><span class="input-count"></span>
							<div class="errors"></div>
							</div>
							@include('_forms.hp-page.parts.partials.text-util')
						</dd>
					</dl>
					<dl>
						<dt>リンク</dt>
						<dd>
							<?php $element->form('link')?><span class="input-count"></span>
							<div class="errors"></div>
						</dd>
					</dl>
					<dl>
						<dt>画像</dt>
						<dd>
							<?php $elementName = 'image'?>
							<?php $titleName = 'image_title'?>
							<div class="select-image">
								<a href="javascript:void(0);">
									<?php if($imageId = $element->getElement($elementName)->getValue()):?>
									<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
									<?php else:?>
									<span>画像の追加</span>
									<?php endif;?>
								</a>
								<?php $element->simpleHidden($elementName); ?>

								<?php if($imageId = $element->getElement($elementName)->getValue()):?>
			                    <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
			                    <?php else:?>
			                    <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
			                    <?php endif;?>

								<div class="errors"></div>
								<?php if ($element->getElement($titleName)):?>
								<div class="is-require select-image-title">
									<label><?php echo $element->getElement($titleName)->getLabel(); ?><i class="i-l-require">必須</i></label>
									<?php $element->simpleText($titleName); ?><span class="input-count">0/30</span>
									<div class="errors"></div>
								</div>
								<?php endif;?>
							</div>
						</dd>
					</dl>
				</div>
				<?php endforeach;?>
				
				<div class="item-add">
					<a href="javascript:void(0);" class="btn-t-blue size-s">施設を追加</a>
				</div>
				
			</div>
		</div>
		<?php endforeach;?>
		
		<div class="item-add">
			<a href="javascript:void(0);" class="btn-t-blue size-s">カテゴリを追加</a>
		</div>
	</div>
</div>