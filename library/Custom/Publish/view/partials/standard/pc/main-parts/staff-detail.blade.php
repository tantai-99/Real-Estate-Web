<div class="element element-tximg2">
    <div class="element-left">
        <?php if ($view->element->getValue('position') !== '0' || $view->element->getValue('shop_name')) : ?>
            <p class="fs12">
                <?php if ($view->element->getValue('position') !== '0') echo implode('/', $view->optionValues($view->element->position->getValueOptions(), $view->element->getValue('position'))) ?>
                <?php if ($view->element->getValue('position') !== '0' && $view->element->getValue('shop_name')): ?>
                    /
                <?php endif ?>
                <?php if ($view->element->getValue('shop_name')): ?>
                    <?php echo h($view->element->getValue('shop_name')) ?>
                <?php endif ?>
            </p>
        <?php endif ?>
        <section>
            <?php if ($view->element->name->getValue() || $view->element->kana->getValue()): ?>
                <h3 class="element-heading">
                    <?php if ($view->element->name->getValue()): ?>
                        <?php echo h($view->element->name->getValue()) ?>
                    <?php endif ?>
                    <?php if ($view->element->kana->getValue()): ?>
                        <span class="fs12">（<?php echo h($view->element->kana->getValue()) ?>）</span>
                    <?php endif ?>
                </h3>
            <?php endif ?>

            <?php if ($view->element->birthplace->getValue() || $view->element->hobby->getValue() || $view->element->qualification->getValue()): ?>
                <dl class="area-profile">
                    <?php if ($view->element->birthplace->getValue()): ?>
                        <dt>出身：</dt>
                        <dd class="birthplace"><?php echo h($view->element->birthplace->getValue()) ?></dd>
                    <?php endif ?>
                    <?php if ($view->element->hobby->getValue()): ?>
                        <dt>趣味：</dt>
                        <dd class="hobby"><?php echo h($view->element->hobby->getValue()) ?></dd>
                    <?php endif ?>
                    <?php if ($view->element->qualification->getValue()): ?>
                        <dt>資格：</dt>
                        <dd class="qualification">
                            <?php echo implode('/', $view->optionValues($view->element->qualification->getValueOptions(), $view->element->qualification->getValue())) ?>
                        </dd>
                    <?php endif ?>
                </dl>
            <?php endif ?>

            <?php if ($view->element->getValue('pr')): ?>
                <div class="rp_comment">
                    <p><?php echo $view->element->getValue('pr') ?></p>
                </div>
            <?php endif ?>
        </section>
    </div>
    <p class="element-right">
        <?php if ($view->element->getValue('image')): ?>
            <img src="<?php echo $view->hpImage($view->element->getValue('image')) ?>" alt="<?php echo h($view->element->getValue('image_title')) ?>"/>
        <?php endif ?>
    </p>
</div>


<div class="element">
    <table class="element-table element-table4">
        <?php
        $index = 0;
        $last_index = count($view->element->elements->getSubForms()) - 1;
        $is_odd = count($view->element->elements->getSubForms()) % 2 !== 0;
        ?>
        <?php foreach ($view->element->elements->getSubForms() as $el) : ?>
            <?php if ($index % 2 === 0): ?>
                <tr>
            <?php endif ?>
            <th>
                <?php $title = $el->getTitle();; ?>
                <?php echo h($title) ?>
            </th>
            <td>
                <?php if ($title == 'ブログ') : ?>
                    <?php $url = h($el->getValue('value')); ?>
                    <a href="<?php echo $url; ?>"><?php echo $url; ?></a>
                <?php else : ?>
                    <?php echo h($el->getValue('value')) ?>
                <?php endif; ?>
            </td>
            <?php if ($index === $last_index && $is_odd): ?>
                <th></th>
                <td></td>
            <?php endif ?>
            <?php if ($index % 2 !== 0 || $index === $last_index): ?>
                </tr>
            <?php endif ?>
            <?php $index++ ?>
        <?php endforeach ?>
    </table>
</div>
