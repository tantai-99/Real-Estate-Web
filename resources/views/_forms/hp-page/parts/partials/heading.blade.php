<?php if($element->hasHeading()):?>
<dl class="item-header<?php if($element->getElement('heading')->isRequired()):?>  is-require<?php endif;?>">
  <dt>
    <?php if ($element->getElement('heading_type')):?>
    <?php $element->form('heading_type')?>
    <div class="errors"></div>
    <?php else:?>
    <span>見出し</span>
    <?php endif;?>
  </dt>
  <dd>
    <?php $element->form('heading')?>
    <span class="input-count"></span>
    <div class="errors"></div>
  </dd>
</dl>
<?php endif;?>