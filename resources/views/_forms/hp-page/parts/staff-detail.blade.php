<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
  @include('_forms.hp-page.parts.partials.header', ['element' => $element])
  <div class="page-element-body sortable-item-container">
    @include('_forms.hp-page.parts.partials.heading', ['element' => $element])

      <div class="item-list">
          <dl class="add-item is-require">
              <dt><span>店舗名<i class="i-l-require">必須</i></span></dt>
              <dd>
                  <?php $element->form('shop_name')?><span class="input-count"></span>
                  <div class="errors"></div>
              </dd>
          </dl>
      </div>

      <div class="item-list">
          <dl class="add-item">
              <dt><span>役職</span></dt>
              <dd>
                  <?php $element->form('position')?><span class="input-count"></span>
                  <div class="errors"></div>
              </dd>
          </dl>
      </div>

      <div class="item-list">
        <dl class="add-item is-require">
            <dt><span>氏名<i class="i-l-require">必須</i></span></dt>
            <dd>
                <?php $element->simpleText('name')?><span class="input-count"></span>
                <div class="errors"></div>
            </dd>
        </dl>
    </div>
    
    <div class="item-list">
        <dl class="add-item is-require">
            <dt><span>ふりがな<i class="i-l-require">必須</i></span></dt>
            <dd>
                <?php $element->simpleText('kana')?><span class="input-count"></span>
                <div class="errors"></div>
            </dd>
        </dl>
    </div>
    
    <div class="item-list">
        <dl class="item-group">
            <dt>画像</dt>
            <dd>
                <div class="select-image">
                    <a href="javascript:void(0);">
                        <?php if($imageId = $element->getElement('image')->getValue()):?>
                        <img src="/image/hp-image?image_id=<?php echo h($imageId)?>" alt="" />
                        <?php else:?>
                        <span>画像の追加</span>
                        <?php endif;?>
                    </a>
                    <?php $element->simpleHidden('image'); ?>
                    <?php if($imageId = $element->getElement('image')->getValue()):?>

                    <p class="select-image__tx_annotation">「画像」をクリックして画像フォルダから変更してください。</p>
                    <?php else:?>
                    <p class="select-image__tx_annotation">「画像の追加」をクリックして画像フォルダから追加してください。</p>
                    <?php endif;?>
                    
                    <div class="errors"></div>
                    <div class="is-require select-image-title">
                        <label>画像タイトル<i class="i-l-require">必須</i></label>
                        <?php $element->simpleText('image_title'); ?><span class="input-count">0/30</span>
                        <div class="errors"></div>
                    </div>
                </div>
            </dd>
        </dl>
    </div>
    
    <div class="item-list">
        <dl class="add-item">
            <dt><span>出身地</span></dt>
            <dd>
                <?php $element->simpleText('birthplace')?><span class="input-count"></span>
                <div class="errors"></div>
            </dd>
        </dl>
    </div>
    
    <div class="item-list">
        <dl class="add-item">
            <dt><span>趣味</span></dt>
            <dd>
                <?php $element->simpleText('hobby')?><span class="input-count"></span>
                <div class="errors"></div>
            </dd>
        </dl>
    </div>
    
    <div class="item-list">
        <dl class="add-item">
            <dt><span>資格</span></dt>
            <dd class="input-check-set">
                <?php $element->form('qualification')?><span class="input-count"></span>
                <div class="errors"></div>
            </dd>
        </dl>
    </div>
    
    <div class="item-list">
        <dl class="add-item is-require">
            <dt><span>PRコメント<i class="i-l-require">必須</i></span></dt>
            <dd class="element-text-utilcontainer element-text">
            	<div class="mb20">
                <?php $element->simpleText('pr')?><span class="input-count"></span>
                <div class="errors"></div>
                </div>
                @include('_forms.hp-page.parts.partials.text-util')
            </dd>
        </dl>
    </div>
    
    
    <?php $subForms = $element->getSubForm('elements')->getSubForms()?>
    <?php foreach ($subForms as $key => $form):?>
        <div class="item-list sortable-item added-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
            <?php $form->simpleHidden('type')?>
            <?php $form->simpleHidden('sort')?>
            
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
                    <?php if ($form->getElement('value') instanceof Library\Custom\Form\Element\Select):?>
                    <?php $form->simpleSelect('value')?>
                    <?php else:?>
                    <?php $form->simpleText('value')?><span class="input-count"></span>
                    <?php endif;?>
                    <div class="errors"></div>
                </dd>
                <dd class="action">
                    <a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
                    <a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
                    <a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
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