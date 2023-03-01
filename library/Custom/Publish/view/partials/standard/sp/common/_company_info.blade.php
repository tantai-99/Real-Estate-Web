<div class="company-info">
    <?php if ($view->hp->tel) : ?>
        <p class="company-tel">
            <a href="<?php echo 'tel:'.mb_ereg_replace('[^0-9]', '', $view->hp->tel); ?>"><span><?php echo h($view->hp->tel); ?></span></a>
        </p>
    <?php endif; ?>
    <?php if ($view->hp->office_hour): ?>
        <p class="company-time">営業時間/<?php echo h($view->hp->office_hour); ?></p>
    <?php endif; ?>
    <p class="company-name"><?php echo h($view->hp->company_name); ?></p>
    <address class="company-address"><?php echo h($view->hp->adress); ?></address>
</div>