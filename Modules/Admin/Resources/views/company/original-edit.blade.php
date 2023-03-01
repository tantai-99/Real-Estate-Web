@extends('admin::layouts.default')
@section('title', $view->original_edit_title)
@section('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
@stop
@section('script')
<script type="text/javascript" src="/js/admin/preview_top.js"></script>
@stop
@section('content')
<div class="main-contents">
    <div class="main-contents-body top-original-list original">
        <div class="section head-section">
            <div class="left">
                <div class="item title">
                    <h1><?php echo $view->original_edit_title; ?></h1>
                </div>
                <div class="item btns-content">
                    <div class="item"><?php echo $text->get('preview_text');?></div>
                    <div class="item btns">
                        <a href="#" class="btn-p-pc btn-preview" data-type="pc"></a>
                        <a href="#" class="btn-p-sp btn-preview" data-type="sp"></a>
                    </div>
                </div>
            </div>
            <div class="right">
                <a href="/admin/company/detail/?id=<?php echo h($view->params['company_id']);?>" class="btn-t-gray">戻る</a>
            </div>
        </div>
        <div class="section">
            <h4><?php echo $view->original_edit_sub_title; ?></h4>
            <div class="original-transit">
                <?php foreach ($view->original_link as $link) : ?>
                    <?php $target = (isset($link['open_new_tab']) && $link['open_new_tab'] == true) ? 'target="_blank"' : ''; ?>
                    <div><a href="<?php echo $link['url']; ?>" <?php echo $target;?> ><?php echo $link['name']; ?></a></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
@stop
