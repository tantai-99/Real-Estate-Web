<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
  @include('_forms.hp-page.parts.partials.header', ['element' => $element])
  <div class="page-element-body sortable-item-container">
    @include('_forms.hp-page.parts.partials.heading', ['element' => $element])

    <div class="item-list">
        <dl class="add-item is-require">
            <dt><span>PRコメント</span><i class="i-l-require">必須</i></dt>
            <dd class="element-text-utilcontainer element-text">
                <div class="mb20">
                <?php $element->simpleText('pr')?><span class="input-count"></span>
                <div class="errors"></div>
                </div>
                @include('_forms.hp-page.parts.partials.text-util')
            </dd>
        </dl>
    </div>
    <div class="item-list">
        <dl class="item-group">
            <dt>画像</dt>
            <?php for ($i=1;$i<=2;$i++):?>
            <?php
                $name = 'image'.$i;
                $titleName = $name . '_title';
            ?>
            
            <dd>
                <div class="select-image">
                    <a href="javascript:void(0);">
                        <?php if($imageId = $element->getElement($name)->getValue()):?>
                        <img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
                        <?php else:?>
                        <span>画像の追加</span>
                        <?php endif;?>
                    </a>
                    <?php $element->simpleHidden($name); ?>

                    <?php if($imageId = $element->getElement($name)->getValue()):?>
                    <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
                    <?php else:?>
                    <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
                    <?php endif;?>
                    
                    <div class="errors"></div>
                    <div class="is-require select-image-title">
                        <label>画像タイトル<i class="i-l-require">必須</i></label>
                        <?php $element->simpleText($titleName); ?><span class="input-count">0/30</span>
                        <div class="errors"></div>
                    </div>
                </div>
            </dd>
            <?php endfor;?>
        </dl>
    </div>
    
    
    <?php $subForms = $element->getSubForm('elements')->getSubForms()?>
    <?php foreach ($subForms as $key => $form):?>
        <div class="item-list sortable-item added-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
            <?php $form->simpleHidden('type')?>
            <?php $form->simpleHidden('sort')?>
            
            <dl class="add-item<?php if($element->isRequiredType($form->getElement('type')->getValue())):?> is-require<?php endif;?>">
                <dt>
                    <?php if ($form->getElement('title')):?>
                    <?php $form->simpleText('title')?><span class="input-count"></span>
                    <div class="errors"></div>
                    <?php else:?>
                    <span><?php echo $form->getTitle()?></span><?php if($element->isRequiredType($form->getElement('type')->getValue())):?><i class="i-l-require">必須</i><?php endif;?>
                    <?php endif;?>
                </dt>
                <dd>
                    <?php $form->simpleText('value')?><span class="input-count"></span>
                    <div class="errors"></div>
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn<?php if($element->isRequiredType($form->getElement('type')->getValue())):?> is-disable<?php endif;?>">削除</a>
                </dd>
            </dl>
        </div>
    <?php endforeach;?>
    
    <div class="item-add">
      <?php if (count($element->getElementTypes()) > 1):?>
      <label>行を追加</label>
      <select></select>
      <?php endif;?>
      <a href="javascript:void(0);" class="btn-t-blue size-s">追加</a>
    </div>
  </div>
</div>