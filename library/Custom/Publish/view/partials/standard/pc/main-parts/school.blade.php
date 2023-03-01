<?php
$has_value = false;
foreach ($view->element->elements->getSubForms() as $area) {
    if ($area->getValue('category')) {
        $has_value = true;
        break;
    }
    foreach ($area->elements->getSubForms() as $school) {
        if (!$school->getValue('name') && !$school->getValue('school_zoning')) {
            continue;
        }
        $has_value = true;
        break;
    }
    if ($has_value) {
        break;
    }
}

if (!$has_value) {
    return;
}
?>
<div class="element">
    <table class="element-table element-table1">
        <?php foreach ($view->element->elements->getSubForms() as $area): ?>
            <tr>
                <th colspan="2" class="element-table-heading">
                    <?php echo h($area->getValue('category')) ?>
                </th>
            </tr>
            <?php foreach ($area->elements->getSubForms() as $school): ?>
                <?php if (!$school->getValue('name') && !$school->getValue('school_zoning')) {
                    continue;
                }?>
                <tr>
                    <th>
                        <?php echo h($school->getValue('name')) ?>
                    </th>
                    <td>
                        <?php echo h($school->getValue('school_zoning')) ?>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php endforeach ?>
    </table>
</div>
