<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
    @include('_forms.hp-page.parts.partials.header', [
        'element' => $element
    ])
  <div class="page-element-body sortable-item-container">
    @include('_forms.hp-page.parts.partials.heading', [
          'element' => $element
      ])
    <div class="item-list">
    <dl>
        <dt><?php echo $element->getElement('page_size')->getLabel()?></dt>
        <dd>
            <?php echo $element->simpleSelect('page_size')?>
            <div class="errors"></div>
        </dd>
    </dl>
    </div>
    
  </div>
</div>