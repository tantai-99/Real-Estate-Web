<?php use App\Repositories\HpPage\HpPageRepository; ?>
<?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $view->tag) : ?>
    <?php // 会社問い合わせ ?>
    <?php if ($view->page_type_code == HpPageRepository::TYPE_FORM_CONTACT) : ?>
        <?php echo PHP_EOL.'<?php if ($this->isInputPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_contact_input.PHP_EOL; ?>
        <?php echo '<?php elseif ($this->isThanksPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_contact_thanks.PHP_EOL; ?>
        <?php echo '<?php endif ; ?>'.PHP_EOL; ?>
    <?php endif;?>
    <?php // 資料請求 ?>
    <?php if ($view->page_type_code == HpPageRepository::TYPE_FORM_DOCUMENT) : ?>
        <?php echo PHP_EOL.'<?php if ($this->isInputPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_request_input.PHP_EOL; ?>
        <?php echo '<?php elseif ($this->isThanksPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_request_thanks.PHP_EOL; ?>
        <?php echo '<?php endif ; ?>'.PHP_EOL; ?>
    <?php endif;?>
    <?php // 査定依頼 ?>
    <?php if ($view->page_type_code == HpPageRepository::TYPE_FORM_ASSESSMENT) : ?>
        <?php echo PHP_EOL.'<?php if ($this->isInputPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_assess_input.PHP_EOL; ?>
        <?php echo '<?php elseif ($this->isThanksPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_assess_thanks.PHP_EOL; ?>
        <?php echo '<?php endif ; ?>'.PHP_EOL; ?>
    <?php endif;?>

    <?php // 物件リクエスト ?>
    <?php // 居住用賃貸物件フォーム ?>
    <?php if ($view->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE) : ?>
        <?php echo PHP_EOL.'<?php if ($this->isInputPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_residential_rental_request_input.PHP_EOL; ?>
        <?php echo '<?php elseif ($this->isThanksPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_residential_rental_request_thanks.PHP_EOL; ?>
        <?php echo '<?php endif ; ?>'.PHP_EOL; ?>
    <?php endif;?>
    <?php // 事務所用賃貸物件フォーム ?>
    <?php if ($view->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE) : ?>
        <?php echo PHP_EOL.'<?php if ($this->isInputPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_business_rental_request_input.PHP_EOL; ?>
        <?php echo '<?php elseif ($this->isThanksPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_business_rental_request_thanks.PHP_EOL; ?>
        <?php echo '<?php endif ; ?>'.PHP_EOL; ?>
    <?php endif;?>
    <?php // 居住用売買物件フォーム ?>
    <?php if ($view->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY) : ?>
        <?php echo PHP_EOL.'<?php if ($this->isInputPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_residential_sale_request_input.PHP_EOL; ?>
        <?php echo '<?php elseif ($this->isThanksPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_residential_sale_request_thanks.PHP_EOL; ?>
        <?php echo '<?php endif ; ?>'.PHP_EOL; ?>
    <?php endif;?>
    <?php // 事務所用売買物件フォーム ?>
    <?php if ($view->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY) : ?>
        <?php echo PHP_EOL.'<?php if ($this->isInputPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_business_sale_request_input.PHP_EOL; ?>
        <?php echo '<?php elseif ($this->isThanksPage()) : ?>'.PHP_EOL; ?>
        <?php echo $view->tag->above_close_body_tag_business_sale_request_thanks.PHP_EOL; ?>
        <?php echo '<?php endif ; ?>'.PHP_EOL; ?>
    <?php endif;?>


<?php endif;?>