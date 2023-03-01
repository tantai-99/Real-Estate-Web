<div class="page-element sortable-item element-table" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
  <?php // echo $partial('_forms/hp-page/parts/partials/header.phtml', array('element' => $element)); ?>
  @include('_forms.hp-page.parts.partials.header', ['element' => $element])
  <div class="page-element-body sortable-item-container ewrapper">
  <?php // echo $partial('_forms/hp-page/parts/partials/heading.phtml', array('element' => $element)); ?>
  @include('_forms.hp-page.parts.partials.heading', ['element' => $element])
  <dl class="item-header">
		<dt>
            <?php echo $element->getElement('heading')->getLabel()?><?php echo $view->toolTip('page_parts_estatekomasearcher_heading')?>
		</dt>
		<dd>
            <?php $element->form('heading')?>
            <span class="input-count"></span>
            <div class="errors"></div>
		</dd>
  </dl>
  <dl class="item-header">
		<dt>
            <?php echo $element->getElement('htmltagpc')->getLabel()?><?php echo $view->toolTip('page_parts_estatekomasearcher_htmltag')?>
		</dt>
		<dd>
            <?php $element->form('htmltagpc')?>
            <div class="errors"></div>
		</dd>
  </dl>
  <dl class="item-header">
		<dt>
            <?php echo $element->getElement('htmltagsp')->getLabel()?><?php echo $view->toolTip('page_parts_estatekomasearcher_htmltag')?>
		</dt>
		<dd>
            <?php  $element->form('htmltagsp')?>
            <div class="errors"></div>
		</dd>
  </dl>
  <div class="notice-search-er">
    検索エンジンレンタルのコントロールパネルより特集コマの「HTMLタグ」を取得して貼り付けてください。<br>
    設置可能なサイズは以下になりますので、「HTMLタグ」取得時に以下のサイズから指定してください。<br>
    PC用　　　　：大きさ「大」「中」 横1～3列・縦1～20行 ／ 大きさ「小」 横1～4列・縦1～20行<br>
    スマートフォン用：縦1～20行
  </div>
  </div>
  
</div>
<style>
    .notice-search-er{
        font-size: 11px;
    } 
</style>