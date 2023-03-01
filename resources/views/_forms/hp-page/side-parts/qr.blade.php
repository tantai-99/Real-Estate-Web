<div class="page-element sortable-item element-text" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
	@include('_forms.hp-page.side-parts.partials.header', ['element' => $element])
	<div class="page-element-body">
		@include('_forms.hp-page.side-parts.partials.heading', ['element' => $element])
	</div>
</div>

