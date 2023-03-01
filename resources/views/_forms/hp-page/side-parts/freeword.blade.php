<div class="page-element sortable-item element-text" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
<div class="page-element-header">
    <h3><span><?php echo $element->getTitle(); ?></span><?php echo $view->toolTip('page_parts_freeword_heading');?></h3>
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
	<div class="page-element-body">
        <p><?php echo $element->getTitle()?>が表示されます。<br>※物件検索設定にて「フリーワード」を「利用しない」に設定した場合や物件検索設定を削除した場合は、公開されたホームページにこの項目は表示されません。</p>
        <table class="table-search freeword-table">
            <?php foreach ($element->getElements() as $name  => $ele) : ?>
                <?php if (get_class($ele) == "Library\Custom\Form\Element\Hidden") continue; ?>
                <tr>
				<?php if ($name == 'display_any') { ?>
                    <td>
						<div style="display:none;"><?php $element->form($name) ?></div>
						<div class="errors"></div>
                    </td>
				<?php } else { ?>
                    <td>
                        <dl>
                            <dt>
                                <?php echo $ele->getLabel() ?>
                            </dt>
                            <dd style="margin-top: 6px;">
								<input type="checkbox" class="fw-display"/>表示する
								<span class="updown" style="margin-left: 50px;">
									<a href="javascript:void(0);" class="fw-up-btn"><i class="i-e-up" style="display:inline-flex;">上へ移動</i></a>
									<a href="javascript:void(0);" class="fw-down-btn"><i class="i-e-down" style="display:inline-flex;">下へ移動</i></a>
								</span>
								<div style="display:none;">
                                <?php $element->form($name) ?>
								</div>
                                <div class="errors"></div>
                            </dd>
                        </dl>
                    </td>
				<?php } ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>


