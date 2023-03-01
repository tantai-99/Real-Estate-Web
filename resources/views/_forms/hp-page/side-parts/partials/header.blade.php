<div class="page-element-header">
  <h3><span><?php echo $element->getTitle(); ?></span><?php echo $view->toolTip('page_side_parts_'.$element->getTypeName())?></h3>
  <?php $element->form('parts_type_code')?>
  <?php $element->form('sort')?>
  <?php $element->form('display_flg')?>
  <div class="errors is-hide"></div>
  
  <ul class="page-element-menu">
    <li><a href="javascript:void(0);" class="up-btn"><i class="i-e-up">上へ移動</i></a></li>
    <li><a href="javascript:void(0);" class="down-btn"><i class="i-e-down">下へ移動</i></a></li>
    <li class="pull">
      <a href="javascript:void(0);"><i class="i-e-set">操作</i></a>
      <ul>
        <?php /*<li><a href="javascript:void(0);" class="close-btn"><i class="i-e-close"></i>非表示</a></li>*/?>
        <li><a href="javascript:void(0);" class="delete-btn"><i class="i-e-delete"></i>削除</a></li>
      </ul>
    </li>
  </ul>
</div>