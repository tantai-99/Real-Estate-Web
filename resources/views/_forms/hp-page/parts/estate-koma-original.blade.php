<?php
#old spec
//$housingBlockControl = getInstanceUser('cms')->isNerfedTop();
#3603 - now always true
$housingBlockControl = true;
?>
<div class="page-element sortable-item element-table top-original-list" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
    <div class="housing-block-title">
        <div class="section-title"><?php echo $element->special_title->getValue();?></div>
        <div class="small-title">
            <span class="title">( 特集ID:
                <b><?php echo $element->special_id->getValue();?></b>
            </span>
            <span class="title" >ステータス:
                <b class="<?php echo ($element->is_public->getValue()) ? 'text-danger' : ''; ?>">
                    <?php echo ($element->is_public->getValue()) ? '公開中' : '下書き';?>
                </b>
            </span>
            <span class="title">
                物件種目: <b><?php echo $element->special_type->getValue();?></b>)
            </span>
        </div>
    </div>

    <div class="page-element-header housing-block-koma-title">
        <h3><span>下記で表示制御が可能です</span></h3>
    </div>
    <table class="tb-basic tb-centered tb-housing-block-list">
        <tr>
            <?php $element->simpleHidden('id');?>
            <?php $element->simpleHidden('parts_type_code');?>
            <?php $element->simpleHidden('special_id');?>
            <td>PC版</td>
            <td>↓列</td>
            <td>

                <?php if ($housingBlockControl && 1 == $element->pc_columns_disable->getValue()): ?>
                    <?php $element->simpleHidden('pc_columns'); ?>
                    <div class="select-ct disabled-item"><?php echo $element->pc_columns->getValue(); ?></div>
                <?php else: ?>
                    <div class="select-custom-agency select-ct">
                        <?php echo $element->simpleSelect('pc_columns'); ?>
                        <div class="sel-custom-agency-koma"><?php echo $element->pc_columns->getValue(); ?></div>
                        <div class="errors"></div>
                    </div>
                <?php endif ?>
            </td>
            <td>→行</td>
            <td>

                <?php if ($housingBlockControl && 1 == $element->pc_rows_disable->getValue()): ?>
                    <?php $element->simpleHidden('pc_rows'); ?>
                    <div class="select-ct disabled-item"><?php echo $element->pc_rows->getValue(); ?></div>
                <?php else: ?>
                    <div class="select-custom-agency select-ct">
                        <?php echo $element->simpleSelect('pc_rows'); ?>
                        <div class="sel-custom-agency-koma"><?php echo $element->pc_rows->getValue(); ?></div>
                        <div class="errors"></div>
                    </div>
                <?php endif ?>

            </td>
            <td>SP版</td>
            <td>↓列</td>
            <td>
                <?php if ($housingBlockControl && 1 == $element->sp_columns_disable->getValue()): ?>
                    <?php $element->simpleHidden('sp_columns'); ?>
                    <div class="select-ct disabled-item"><?php echo $element->sp_columns->getValue(); ?></div>
                <?php else: ?>
                    <div class="select-custom-agency select-ct">
                        <?php echo $element->simpleSelect('sp_columns');?>
                        <div class="sel-custom-agency-koma"><?php echo $element->sp_columns->getValue(); ?></div>
                        <div class="errors"></div>
                    </div>
                <?php endif ?>
            </td>
            <td>→行</td>
            <td>
                <?php if ($housingBlockControl && 1 == $element->sp_rows_disable->getValue()): ?>
                    <?php $element->simpleHidden('sp_rows'); ?>
                    <div class="select-ct disabled-item"><?php echo $element->sp_rows->getValue();?></div>
                <?php else: ?>
                    <div class="select-custom-agency select-ct">
                        <?php echo $element->simpleSelect('sp_rows');?>
                        <div class="sel-custom-agency-koma"><?php echo $element->sp_rows->getValue(); ?></div>
                        <div class="errors"></div>
                    </div>
                <?php endif ?>
            </td>
            <td><?php echo $element->sort_option->getLabel();?></td>
            <td class="select-agency">
             <div class="select-custom-agency select-ct">
             <?php $listOptions = $element->sort_option->getValueOptions();
                $label = $listOptions[$element->sort_option->getValue()]; ?>
             <?php echo $element->simpleSelect('sort_option');?><div class="sel-custom-agency-koma"><?php echo $label; ?></div><div class="errors"></div>
             </div>
            </td>
        </tr>
    </table>
</div>
