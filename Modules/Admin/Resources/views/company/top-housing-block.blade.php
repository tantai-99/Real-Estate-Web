@extends('admin::layouts.default')
@section('title', $view->title)
@section('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
@stop
@section('script')
<script type="text/javascript" src="/js/admin/preview_top.js"></script>
<script type="text/javascript" src="/js/admin/top-settings/special.js"></script>
@stop
<?php
    $params = $view->params;
    $form = $view->form;
    $validate = !is_null($view->hp) && !is_null($view->settings);
?>
@section('content')

<div class="main-contents">
    <form id="housing_block" name="housing_block" enctype="application/x-www-form-urlencoded" method="post" action="">
    
    <div class="main-contents-body top-original-list top-housing-block">

        <div class="section head-section">


            <div class="left">
                <div class="item title">
                    <h2><?php echo $view->title ?></h2>
                </div>

                <div class="item btns-content">
                    <div class="item"><?php echo $text->get('preview_text');?></div>
                    <div class="item btns">
                        <a href="#" class="btn-p-pc btn-preview" data-type="pc"></a>
                        <a href="#" class="btn-p-sp btn-preview" data-type="sp"></a>
                    </div>
                </div>

                <div class="item">
                    <button data-id="<?php echo $params['companyId'];?>" class="btn btn-t-gray read-special-btn" name="read" <?php echo (!$validate)?'disabled':'';?> >
                        <?php echo $text->get('special_estate.setting.read_specials');?>
                    </button>
                    <?php //echo $form->getElement('submit')->setDecorators(array('ViewHelper'))->setAttribs(array('disable' => !$validate));?>
                    <input type="submit" name="submit" id="submit" value="<?php echo $text->get('special_estate.setting.save_specials'); ?>" class="btn-t-blue submit-special-setting" <?php echo (!$validate)?'disabled':'';?>>
                </div>
            </div>

            <div class="right">
                <a href="<?php echo $params['backTOP'];?>" class="btn btn-t-gray btn-redirect">
                    <?php echo $text->get('back_to_top_detail');?>
                </a>
            </div>

        </div>

        <div class="clearfix"></div>


        <div id="komas">
            <?php if(!is_null($view->hp) && !is_null($view->settings)):?>
                <?php
                    // echo $view->partial('/company/top-housing-block-partials/content.phtml', [
                    //    'komas' => $form->getSubForms(),
                    //    'links' => $view->links,
                    //    'text' => $view->text
                    // ]);
                ?>
                @include('admin::company.top-housing-block-partials.content', [
                        'komas' => $form->getSubForms(),
                        'links' => $view->links,
                        'text' => $text
                ])
            <?php endif;?>
        </div>
    </div>
    </form>
</div>
@stop