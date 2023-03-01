<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
  @include('_forms.hp-page.parts.partials.header', ['element' => $element])
  <div class="page-element-body sortable-item-container">
    @include('_forms.hp-page.parts.partials.heading', ['element' => $element])

    <?php $subForms = $element->getSubForm('elements')->getSubForms(); ?>
    <?php foreach ($subForms as $key => $form):?>
        <div class="item-list sortable-item added-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
            <?php $form->simpleHidden('type')?>
            <?php $form->simpleHidden('sort')?>
            
            <?php if ($form instanceof Library\Custom\Hp\Page\Parts\Element\Select):?>
            <dl class="add-item">
                <dt>
                    <?php if ($form->getElement('title')):?>
                    <?php $form->simpleText('title')?><span class="input-count"></span>
                    <div class="errors"></div>
                    <?php else:?>
                    <?php echo $form->getTitle()?>
                    <?php endif;?>
                </dt>
                <dd>
                    <?php $form->simpleSelect('value')?>
                    <div class="errors"></div>
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
                </dd>
            </dl>
            <?php elseif ($form instanceof Library\Custom\Hp\Page\Parts\Element\Html):?>
            <dl class="add-item">
                <dt>
                    <?php if ($form->getElement('title')):?>
                    <?php $form->simpleText('title')?><span class="input-count"></span>
                    <div class="errors"></div>
                    <?php else:?>
                    <?php echo $form->getTitle()?>
                    <?php endif;?>
                </dt>
                <dd class="element-text-utilcontainer">
                	<div class="element-text">
                		<div class="item-list">
                    		<?php $form->simpleText('value')?><span class="input-count"></span>
                    		<div class="errors"></div>
                    	</div>
                    	@include('_forms.hp-page.parts.partials.text-util')
                    </div>
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
                </dd>
            </dl>
            <?php elseif ($form instanceof Library\Custom\Hp\Page\Parts\Element\Textarea || $form instanceof Library\Custom\Hp\Page\Parts\Element\TextareaFree):?>
            <dl class="add-item">
                <dt>
                    <?php if ($form->getElement('title')):?>
                    <?php $form->simpleText('title')?><span class="input-count"></span>
                    <div class="errors"></div>
                    <?php else:?>
                    <?php echo $form->getTitle()?>
                    <?php endif;?>
                </dt>
                <dd class="element-text-utilcontainer element-text">
                    <div class="mb20">
                    <?php $form->simpleText('value')?><span class="input-count"></span>
                    <div class="errors"></div>
                    </div>
                    @include('_forms.hp-page.parts.partials.text-util')
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
                </dd>
            </dl>
            <?php elseif ($form instanceof Library\Custom\Hp\Page\Parts\Element\Text || $form instanceof Library\Custom\Hp\Page\Parts\Element\TextFree):?>
            <dl class="add-item">
                <dt>
                    <?php if ($form->getElement('title')):?>
                    <?php $form->simpleText('title')?><span class="input-count"></span>
                    <div class="errors"></div>
                    <?php else:?>
                    <?php echo $form->getTitle()?>
                    <?php endif;?>
                </dt>
                <dd>
                    <?php $form->simpleText('value')?><span class="input-count"></span>
                    <div class="errors"></div>
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
                </dd>
            </dl>
            <?php elseif ($form instanceof Library\Custom\Hp\Page\Parts\Element\Image):?>
            <dl class="item-group">
                <dt><?php echo $form->getTitle(); ?></dt>
                <dd>
                    <div class="select-image">
                        <a href="javascript:void(0);">
                            <?php if($imageId = $form->getElement('image')->getValue()):?>
                            <img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
                            <?php else:?>
                            <span>画像の追加</span>
                            <?php endif;?>
                        </a>
                        <?php $form->simpleHidden('image'); ?>

                        <?php if($imageId = $form->getElement('image')->getValue()):?>
                        <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
                        <?php else:?>
                        <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
                        <?php endif;?>

                        <div class="errors"></div>
                        <?php if ($form->getElement('image_title')):?>
                        <div class="is-require select-image-title">
                            <label><?php echo $form->getElement('image_title')->getLabel(); ?><i class="i-l-require">必須</i></label>
                            <?php $form->simpleText('image_title'); ?><span class="input-count">0/30</span>
                            <div class="errors"></div>
                        </div>
                        <?php endif;?>
                    </div>
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
                </dd>
            </dl>
            <?php else:?>
            <dl class="add-item">
                <dt>
                    <?php if ($form->getElement('title')):?>
                    <?php $form->simpleText('title')?><span class="input-count"></span>
                    <div class="errors"></div>
                    <?php else:?>
                    <?php echo $form->getTitle()?>
                    <?php endif;?>
                </dt>
                <dd>
                    <?php $form->simpleText('value')?><span class="input-count"></span>
                    <div class="errors"></div>
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
                </dd>
            </dl>
            <?php endif;?>
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