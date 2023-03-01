<div class="element element-tximg7">
    <p class="element-right">
        <?php if ($view->element->getValue('image')) : ?>
            <img src="<?php echo $view->hpImage($view->element->getValue('image')) ?>"  alt="<?php echo h($view->element->getValue('image_title')) ?>"/>
        <?php endif ?>
    </p>

    <?php if ($view->element->getValue('area')): ?>
        <p class="fs12"><?php echo h($view->element->getValue('area')) ?></p>
    <?php endif ?>

    <?php if ($view->element->getValue('customer_name') || $view->element->getValue('customer_age') || $view->element->getValue('structure_type') || $view->element->getValue('staff_name')): ?>
    <section>
        <?php if ($view->element->getValue('customer_name')): ?>
            <h3 class="element-heading"><?php echo h($view->element->getValue('customer_name')) ?></h3>
        <?php endif ?>
        <?php if ($view->element->getValue('customer_age') || $view->element->getValue('structure_type') || $view->element->getValue('staff_name')): ?>
            <dl class="area-profile">
                <?php if ($view->element->getValue('customer_age')): ?>
                    <dt>年齢：</dt>
                    <dd class="customer_age"><?php echo h($view->element->getValue('customer_age')) ?>歳</dd>
                <?php endif ?>
                <?php if ($view->element->getValue('structure_type')): ?>
                    <dt>ご契約種別：</dt>
                    <dd class="structure_type">
                        <?php echo implode('/', $view->optionValues($view->element->structure_type->getValueOptions(), $view->element->structure_type->getValue('value'))) ?>
                    </dd>
                <?php endif ?>
                <?php if ($view->element->getValue('staff_name')): ?>
                    <dt>弊社担当：</dt>
                    <dd class="staff_name"><?php echo h($view->element->getValue('staff_name')) ?></dd>
                <?php endif ?>
            </dl>
        <?php endif ?>
    </section>
    <?php endif ?>

    <section class="customer_comment">
        <h4 class="element-heading2 title"><?php echo h($view->element->getValue('title')) ?></h4>
        <?php if ($view->element->getValue('customer_comment')): ?>
        <p>
            <?php echo $view->element->getValue('customer_comment') ?>
        </p>
        <?php endif ?>
    </section>
</div>

<?php if ($view->element->getValue('staff_comment')): ?>
<div class="element element-comment">
    <section>
        <h4 class="element-heading"><span>スタッフからのコメント</span></h4>
        <p>
            <?php echo $view->element->getValue('staff_comment') ?>
        </p>
    </section>
</div>
<?php endif ?>

<?php if ($view->element->getValue('date')) : ?>
<time class="comment-date"><?php echo h($view->element->getValue('date')) ?></time>
<?php endif ?>
