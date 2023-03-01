<div class="page-element sortable-item element-text" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
	@include('_forms.hp-page.side-parts.partials.header', ['element' => $element])
	<div class="page-element-body">
		<p><?php echo $element->getTitle()?>が表示されます。<br>
			※基本設定にある「タイムラインの設置」が「有効」でない場合、公開されたサイトにタイムラインが表示されません。</p>
	</div>
</div>