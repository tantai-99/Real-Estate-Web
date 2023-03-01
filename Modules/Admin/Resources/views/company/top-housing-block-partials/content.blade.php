<?php
$komas = $komas;
use Library\Custom\Hp\Page\Parts\EstateKoma;
?>

<?php if (!empty($komas)): ?>
    <?php foreach ($komas as $k => $v): ?>
        <?php
        $detail = $v->getSubForm($k . "[detail]");
        ?>
        <div class="section">
            <?php if ($detail): ?>
                <div class="housing-block-title">
                    <div class="large-title">
                        <?php echo $detail->getElement('title')->getValue(); ?>
                    </div>
                    <div class="small-title">
                    <span class="title">( 特集ID:
                        <b><?php echo $detail->getElement('origin_id')->getValue(); ?></b>
                    </span>
                    <span class="title">ステータス:
                        <b class="<?php echo ($detail->getElement('is_public')->getValue()) ? 'text-danger' : ''; ?>">
                            <?php echo $detail->getElement('publish_status')->getValue(); ?>
                        </b>
                    </span>
                    <span class="title">
                        <span> 物件種目: </span><span> <?php echo $detail->getElement('type')->getValue(); ?> </span> )
                    </span>
                    </div>
                </div>
            <?php endif; ?>
            <table class="tb-basic tb-bordered tb-housing-block-list">
                <thead>
                <tr>
                    <th width="10%"><?php echo $text->get('special_estate.thead.col_1'); ?></th>
                    <th width="10%"><?php echo $text->get('special_estate.thead.col_2'); ?></th>
                    <th width="15%"><?php echo $text->get('special_estate.thead.col_3'); ?></th>
                    <th width="40%"><?php echo $text->get('special_estate.thead.col_4'); ?></th>
                    <th width="25%"><?php echo $text->get('special_estate.thead.col_5'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-left">
                        <?php
                        // some fields need to generate
                        $cms_editable_name = EstateKoma::CMS_DISABLE;
                        // echo $v->form($cms_editable_name); ?>
                        <input type="hidden" name="<?php echo $v->getElement($cms_editable_name)->getFullName(); ?>" value="<?php echo $v->getElement($cms_editable_name)->getValue(); ?>">
                        <input type="checkbox" id="<?php echo $v->getElement($cms_editable_name)->getId(); ?>" class="<?php echo implode(' ', (array)$v->getElement($cms_editable_name)->getAttribute('class')); ?>" name="<?php echo $v->getElement($cms_editable_name)->getFullName(); ?>" value="1" <?php echo ($v->getElement($cms_editable_name)->getValue()) ? 'checked' : ''; ?>>
                        <?php echo $v->getElement($cms_editable_name)->getLabel();
                        ?>
                    </td>
                    <td class="text-left">
                        <?php if ($detail): ?>
                            <?php
                            $alias = $detail->getElement('alias')->getValue();
                            echo $alias;
                            $detail->getElement('alias')->setValue('{' . $alias . '}');
                            echo $detail->form('alias');
                            ?>
                            <span class="copy-icon"></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        if ($v->getElement('last_update')->getValue()) {
                            echo date('Y年n月j日 H : i', strtotime($v->getElement('last_update')->getValue()));
                        }
                        ?> <br/>
                        <?php echo $v->getElement('update_name')->getValue(); ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex pbt-5">
                            <div class="d-flex flex-start">
                                PC版
                            </div>
                            <div class="d-flex flex-end">
                                <div class="d-flex-content">
                                    <span>⇣列</span>
                                    <div class="select-ct sel-special sm"><?php echo $v->form(EstateKoma::PC_COLUMNS); ?><div class="sel-custom-special"><?php echo $v->getElement(EstateKoma::PC_COLUMNS)->getValue() ?></div></div>
                                </div>
                                <div class="d-flex-content">
                                    <span class="cms-disable-ui">
                                        <?php
                                        $pc_col_label = EstateKoma::PC_COLUMNS_DISABLE;
                                        // echo $v->form($pc_col_label); ?>
                                        <input type="hidden" name="<?php echo $v->getElement($pc_col_label)->getFullName(); ?>" value="<?php echo $v->getElement($pc_col_label)->getValue(); ?>">
                                        <input type="checkbox" id="<?php echo $v->getElement($pc_col_label)->getId(); ?>" class="<?php echo implode(' ', (array)$v->getElement($pc_col_label)->getAttribute('class')); ?>" name="<?php echo $v->getElement($pc_col_label)->getFullName(); ?>" value="1" <?php echo ($v->getElement($pc_col_label)->getValue()) ? 'checked' : ''; ?>><?php echo $v->getElement($pc_col_label)->getLabel();
                                        ?>
                                    </span>
                                </div>
                                <div class="d-flex-content">
                                    <span class="w-10">⇢行</span>
                                    <div class="select-ct sel-special sm w-15"><?php echo $v->form(EstateKoma::PC_ROWS); ?><div class="sel-custom-special"><?php echo $v->getElement(EstateKoma::PC_ROWS)->getValue() ?></div></div>
                                </div>
                                <div class="d-flex-content">
                                    <span class="cms-disable-ui">
                                    <?php
                                        $pc_row_label = EstateKoma::PC_ROWS_DISABLE;
                                        // echo $v->form($pc_row_label); ?>
                                        <input type="hidden" name="<?php echo $v->getElement($pc_row_label)->getFullName(); ?>" value="<?php echo $v->getElement($pc_row_label)->getValue(); ?>">
                                        <input type="checkbox" id="<?php echo $v->getElement($pc_row_label)->getId(); ?>" class="<?php echo implode(' ', (array)$v->getElement($pc_row_label)->getAttribute('class')); ?>" name="<?php echo $v->getElement($pc_row_label)->getFullName(); ?>" value="1" <?php echo ($v->getElement($pc_row_label)->getValue()) ? 'checked' : ''; ?>><?php echo $v->getElement($pc_row_label)->getLabel();
                                    ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex pbt-5">
                            <div class="d-flex flex-start">
                                SP版
                            </div>
                            <div class="d-flex flex-end">
                                <div class="d-flex-content">
                                    <span>⇣列</span>
                                    <div class="select-ct sel-special sm"><?php echo $v->form(EstateKoma::SP_COLUMNS); ?><div class="sel-custom-special"><?php echo $v->getElement(EstateKoma::SP_COLUMNS)->getValue() ?></div></div>
                                </div>
                                <div class="d-flex-content">
                                    <span class="cms-disable-ui">
                                        <?php
                                            $sp_col_label = EstateKoma::SP_COLUMNS_DISABLE;
                                            // echo $v->form($sp_col_label); ?>
                                            <input type="hidden" name="<?php echo $v->getElement($sp_col_label)->getFullName(); ?>" value="<?php echo $v->getElement($sp_col_label)->getValue(); ?>">
                                            <input type="checkbox" id="<?php echo $v->getElement($sp_col_label)->getId(); ?>" class="<?php echo implode(' ', (array)$v->getElement($sp_col_label)->getAttribute('class')); ?>" name="<?php echo $v->getElement($sp_col_label)->getFullName(); ?>" value="1" <?php echo ($v->getElement($sp_col_label)->getValue()) ? 'checked' : ''; ?>><?php echo $v->getElement($sp_col_label)->getLabel();
                                        ?>
                                    </span>
                                </div>
                                <div class="d-flex-content">
                                    <span>⇢行</span>
                                    <div class="select-ct sel-special sm"><?php echo $v->form(EstateKoma::SP_ROWS); ?><div class="sel-custom-special"><?php echo $v->getElement(EstateKoma::SP_ROWS)->getValue() ?></div></div>
                                </div>
                                <div class="d-flex-content">
                                    <span class="cms-disable-ui">
                                    <?php
                                        $sp_row_label = EstateKoma::SP_ROWS_DISABLE;
                                        // echo $v->form($sp_row_label); ?>
                                        <input type="hidden" name="<?php echo $v->getElement($sp_row_label)->getFullName(); ?>" value="<?php echo $v->getElement($sp_row_label)->getValue(); ?>">
                                            <input type="checkbox" id="<?php echo $v->getElement($sp_row_label)->getId(); ?>" class="<?php echo implode(' ', (array)$v->getElement($sp_row_label)->getAttribute('class')); ?>" name="<?php echo $v->getElement($sp_row_label)->getFullName(); ?>" value="1" <?php echo ($v->getElement($sp_row_label)->getValue()) ? 'checked' : ''; ?>><?php echo $v->getElement($sp_row_label)->getLabel();
                                    ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex mr-5 pbt-5">
                            <?php $listOptions = $v->getElement('sort_option')->getValueOptions();
                            $label = $listOptions[$v->getElement('sort_option')->getValue()]; ?>
                            <span class="pr10"><?php echo $v->getElement('sort_option')->getLabel(); ?></span>
                            <div class="select-ct sel-special-sort"><?php echo $v->form('sort_option'); ?><div class="sel-custom-special"><?php echo $label; ?></div></div>
                        </div>

                    </td>
                    <td class="text-center">
                        <a href="<?php echo $links['html']; ?>" class="btn-t-blue">HTML</a>
                        <a href="<?php echo $links['css']; ?>" class="btn-t-blue">css</a>
                        <a href="<?php echo $links['js']; ?>" class="btn-t-blue">js</a>
                        <a href="<?php echo $links['image']; ?>" class="btn-t-blue">image</a>
                    </td>
                </tr>

                </tbody>

            </table>
        </div>
    <?php endforeach; ?>

<?php endif; ?>
