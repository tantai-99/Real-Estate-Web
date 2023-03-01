<div class="page-element sortable-item element-glossary" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<div class="btn-right">
			<a class="btn-t-blue" href="javascript:;">用語を追加</a>
		</div>
		
		<?php
		$nameSyllabary = $element->getElementBelongsTo();
		$bySyllabary = $element->getElementsBySyllabary();
		?>
		<?php foreach ($bySyllabary as $header => $subForms):?>
		<div class="page-element-header" data-kana="<?php echo $header?>" data-kana-header="<?php echo $header?>">
			<h3><?php echo $header?>行</h3>
		</div>
		
		<?php foreach ($subForms as $key => $form):
			$nameSub = '[elements][' .$form->getName().']';
			$form->setElementsBelongsTo($nameSyllabary . $nameSub, null);
		?>
		<div class="item-set sortable-item added-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>" data-kana="<?php $form->getElement('kana')->getValue()?>" data-kana-header="<?php echo $header?>">
			<?php $form->simpleHidden('type')?>
			<?php $form->simpleHidden('sort')?>
			
			<dl class="item-set-header">
				<dt>
					<span>項目</span>
				</dt>
				<dd>
					<div><?php $form->getElement('word')->getValue()?></div>
					<?php $form->simpleHidden('word')?>
					<div class="errors"></div>
				</dd>
				<dd class="action">
					<a href="javascript:void(0);" class="i-e-edit ml20 word-edit-btn">編集</a>
					<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
				</dd>
			</dl>
			
			<div class="item-set-list">
				<dl class="">
					<dt><span>読み（ひらがな）</span></dt>
					<dd>
						<div><?php $form->getElement('kana')->getValue()?></div>
						<?php $form->simpleHidden('kana')?>
						<div class="errors"></div>
					</dd>
				</dl>
				<dl>
					<dt><span>内容</span></dt>
					<dd>
						<div class="wysiwyg-preview"><?php echo $form->getElement('description')->getValue()?></div>
						<?php $form->simpleHidden('description')?>
						<div class="errors"></div>
					</dd>
				</dl>
				<dl>
					<dt><span>画像</span></dt>
					<dd>
						<div style="width:190px;">
						<?php if($imageId = $form->getElement('image')->getValue()):?>
						<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
						<?php endif;?>
						</div>
						<?php $form->simpleHidden('image') ?>
						<div class="errors"></div>
					</dd>
				</dl>
				<dl>
					<dt><span>画像タイトル</span></dt>
					<dd>
						<div><?php $form->getElement('image_title')->getValue()?></div>
						<?php $form->simpleHidden('image_title')?>
						<div class="errors"></div>
					</dd>
				</dl>
			</div>
			
		
		</div>
		<?php endforeach;?>
		<?php endforeach;?>
	</div>
</div>