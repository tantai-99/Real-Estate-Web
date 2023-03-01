<div class="page-element sortable-item" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<div class="item-list">
			<dl class="item-set-header is-require">
				<dt>
					<span>タイトル<i class="i-l-require">必須</i></span>
				</dt>
				<dd>
					<?php echo $element->simpleText('title')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
			</dl>
			
			<dl>
				<dt><span>エリア</span></dt>
				<dd>
					<?php $element->simpleText('area')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
			</dl>
			<dl class="is-require">
				<dt><span>物件種目<i class="i-l-require">必須</i></span></dt>
				<dd>
					<?php $element->simpleSelect('structure_type')?>
					<div class="errors"></div>
				</dd>
			</dl>
			<dl>
				<dt><span>日付</span></dt>
				<dd>
					<?php $element->simpleText('date')?>
					<div class="errors"></div>
				</dd>
			</dl>
			<dl>
				<dt><span>画像</span></dt>
				<dd>
					<?php $elementName = 'image'?>
					<?php $titleName = 'image_title'?>
					<div class="select-image">
						<a href="javascript:;">
							<?php if($imageId = $element->getElement($elementName)->getValue()):?>
							<img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
							<?php else:?>
							<span>画像の追加</span>
							<?php endif;?>
						</a>
						<?php $element->simpleHidden($elementName) ?>

						<?php if($imageId = $element->getElement($elementName)->getValue()):?>
	                    <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
	                    <?php else:?>
	                    <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
	                    <?php endif;?>

						<div class="is-require select-image-title">
							<label>画像タイトル<i class="i-l-require">必須</i></label>
							<?php $element->simpleText($titleName)?><span class="input-count"></span>
							<div class="errors"></div>
						</div>
					</div>
				</dd>
			</dl>
			<dl class="is-require">
				<dt><span>お客さま氏名<i class="i-l-require">必須</i></span></dt>
				<dd>
					<?php $element->simpleText('customer_name')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
			</dl>
			<dl>
				<dt><span>お客さま年齢</span></dt>
				<dd>
					<?php $element->simpleText('customer_age')?>歳
					<div class="errors"></div>
				</dd>
			</dl>
			<dl class="is-require">
				<dt><span>お客さまコメント<i class="i-l-require">必須</i></span></dt>
				<dd class="element-text-utilcontainer element-text">
					<div class="mb20">
					<?php $element->simpleText('customer_comment')?><span class="input-count"></span>
					<div class="errors"></div>
					</div>
					@include('_forms.hp-page.parts.partials.text-util')
				</dd>
			</dl>
			<dl>
				<dt><span>担当スタッフ氏名</span></dt>
				<dd>
					<?php $element->simpleText('staff_name')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
			</dl>
			<dl>
				<dt><span>担当スタッフコメント</span></dt>
				<dd class="element-text-utilcontainer element-text">
					<div class="mb20">
					<?php $element->simpleText('staff_comment')?><span class="input-count"></span>
					<div class="errors"></div>
					</div>
					@include('_forms.hp-page.parts.partials.text-util')
				</dd>
			</dl>
		</div>
	</div>
</div>