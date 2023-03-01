<div class="page-element sortable-item element-glossary" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<div class="item-set">
			
			<div class="item-set-list">
				<dl class="is-require">
					<dt><span>内容</span></dt>
					<dd class="element-text-utilcontainer element-text">
						<div class="mb20">
						<?php $element->simpleText('text')?><span class="input-count"></span>
						<div class="errors"></div>
						</div>
						@include('_forms.hp-page.parts.partials.text-util')
					</dd>
				</dl>
				<dl class="is-require">
					<dt><span>肩書き</span></dt>
					<dd>
						<?php $element->simpleText('title')?><span class="input-count"></span>
						<div class="errors"></div>
					</dd>
				</dl>
				<dl class="is-require">
					<dt><span>署名</span></dt>
					<dd>
						<?php $element->simpleText('signature')?><span class="input-count"></span>
						<div class="errors"></div>
					</dd>
				</dl>
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
			</div>
		
		</div>
		
	</div>
</div>