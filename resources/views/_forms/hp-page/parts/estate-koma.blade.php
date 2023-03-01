<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
  <?php // echo $partial('_forms/hp-page/parts/partials/header.phtml', array('element' => $element)); ?>
  @include('_forms.hp-page.parts.partials.header', ['element' => $element])
  <div class="page-element-body sortable-item-container ewrapper">
    <?php // echo $partial('_forms/hp-page/parts/partials/heading.phtml', array('element' => $element)); ?>
    @include('_forms.hp-page.parts.partials.heading', ['element' => $element])
    
	<dl class="item-header">
		<dt>
			<?php echo $element->getElement('special_id')->getLabel()?>
		</dt>
		<dd>
			<?php echo $element->form('special_id')?>
			<div class="errors"></div>
		</dd>
	</dl>
	<div class="coma-display">
		<dl>
			<dt><?php echo $element->getElement('rows')->getLabel()?><?php echo $view->toolTip('page_parts_estatekoma_rows')?>
			</dt>
			<dd>
				<?php echo $element->form('rows')?>
				<div class="errors"></div>
			</dd>

		</dl>
		<dl class="coma-display-order">
			<dt><?php echo $element->getElement('sort_option')->getLabel()?></dt>
			<dd>
				<?php echo $element->form('sort_option')?>
				<div class="errors"></div>
			</dd>
		</dl>
	</div>
    
  </div>
</div>