<div class="page-element sortable-item element-glossary" 
	data-name="<?php echo $element->getName()?>" 
	data-type="<?php echo $element->getType()?>" 
	data-type-name="<?php echo $element->getTypeName()?>" 
	data-is-unique="<?php echo $element->isUnique()?>" 
	data-has-element="1">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<dl class="item-header is-require">
			<dt><span>画像</span><i class="i-l-require">必須</i></dt>
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

		<dl class="item-header is-require">
			<dt>
				<span>リード文</span><i class="i-l-require">必須</i>
			</dt>
			<dd class="element-text-utilcontainer element-text">
				<div class="mb20">
				<?php $element->simpleText('read_content')?><span class="input-count"></span>
				<div class="errors"></div>
				</div>
				@include('_forms.hp-page.parts.partials.text-util')
			</dd>
		</dl>
		
		<?php $subForms = $element->getSubForm('elements')->getSubForms()?>
		<?php foreach ($subForms as $key => $form):?>
		<div class="item-set sortable-item added-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
			<?php $form->simpleHidden('type')?>
			<?php $form->simpleHidden('sort')?>


			<div class="item-set-list">
				<dl class="item-header is-require">
				  <dt>
				    <span><?php $form->simpleSelect('heading_type')?></span>
				    <div class="errors"></div>
				  </dt>
				  <dd>
				    <?php $form->simpleText('heading')?>
				    <span class="input-count"></span>
				    <div class="errors"></div>
				  </dd>
					<dd class="action">
						<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
						<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
						<a href="javascript:void(0);" class="i-e-delete delete-btn is-disable">削除</a>
					</dd>
				</dl>
				<dl>
					<dt><span>本文</span></dt>
					<dd class="element-text-utilcontainer element-text">
						<div class="mb20">
						<?php $form->simpleText('description')?><span class="input-count"></span>
						<div class="errors"></div>
						</div>
						@include('_forms.hp-page.parts.partials.text-util')
					</dd>
				</dl>
				<dl>
					<dt><span>画像</span></dt>
					<dd>
						<div class="select-image">
							<a href="javascript:;">
								<?php if($imageId = $form->getElement('image')->getValue()):?>
								<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
								<?php else:?>
								<span>画像の追加</span>
								<?php endif;?>
							</a>
							<?php $form->simpleHidden('image') ?>

							<?php if($imageId = $form->getElement('image')->getValue()):?>
	                        <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
	                        <?php else:?>
	                        <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
	                        <?php endif;?>

							<div class="is-require select-image-title">
								<label>画像タイトル<i class="i-l-require">必須</i></label>
								<?php $form->simpleText('image_title')?><span class="input-count"></span>
								<div class="errors"></div>
							</div>
						</div>
					</dd>
				</dl>
			</div>
			
		
		</div>
		<?php endforeach;?>
		
		<div class="item-add">
			<a class="btn-t-blue size-s" href="javascript:void(0);">項目を追加</a>
		</div>
		
	</div>
</div>
