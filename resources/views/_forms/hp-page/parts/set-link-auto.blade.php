<?php
use Library\Custom\Hp\Page\Parts\Element;
?>
<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
  @include('_forms.hp-page.parts.partials.header-link-auto', ['element' => $element])
  <div class="page-element-body sortable-item-container auto-link">
    @include('_forms.hp-page.parts.partials.heading', ['element' => $element])
        <p class="lead-padding">関連コンテンツへのリンクが自動で設置されます。</p>
        <?php if (!$element->isArticle()) : ?>
        <div class="lead">
            <div class="btn-left">
                <p>リード文</p>
            </div>
            <?php if (!$element->isOriginalCategory($element->getPageTypeCode())) : ?>
            <div class="btn-right">
                <a class="auto_link_sample" href="javascript:;" data-type="<?php echo $element->getPageTypeCode()?>">雛形選択</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php $subForms = $element->getSubForm('elements')->getSubForms()?>
    <?php foreach ($subForms as $key => $form):?>
        <div class="item-list sortable-item added-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
            <?php $form->form('type')?>
            <?php $form->form('sort')?>
            <?php if ($form instanceof Element\TextareaLinkAuto && !$element->isArticle()):?>
            <dl class="add-item">
                <dd class="element-text-utilcontainer element-text">
                    <div class="">
                    <?php $form->simpleText('lead')?><span class="input-count"></span>
                    <div class="errors"></div>
                    </div>
                   @include('_forms/hp-page/parts/partials/text-util')
                </dd>
            </dl>
            <?php elseif ($form instanceof Element\Checkbox):?>
            <dl class="add-item">
                <dd>
                    <?php $form->form('contact')?><?php echo $form->getTitle()?>
                    <div class="errors"></div>
                </dd>
            </dl>
            <?php endif;?>
        </div>
    <?php endforeach;?>
  </div>
</div>
