<div class="page-element sortable-item element-access" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<p>中心位置の座標を入力、または地図をクリックしてピンを置いてください。</p>
		<div class="google-map map-area">
		</div>
		<?php $element->simpleHidden('pin_lat')?>
		<?php $element->simpleHidden('pin_lng')?>
		<?php $element->simpleHidden('center_lat')?>
		<?php $element->simpleHidden('center_lng')?>
		<?php $element->simpleHidden('zoom')?>
		<div class="errors"></div>
		
		<div class="item-add">
			<label>中心位置</label>
			<div>
				<input type="text" maxlength="100" class="watch-input-count"><span class="input-count"></span>
			</div>
			<div class="btn-area">
				<a href="javascript:void(0);" class="btn-t-blue size-s">検索</a>
			</div>
		</div>
	</div>
</div>