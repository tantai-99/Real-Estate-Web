@extends('admin::layouts.default')
@section('title', $view->title)
@section('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
@stop
@section('script')
<script type="text/javascript" src="/js/admin/preview_top.js"></script>
@stop
@section('content')
<?php
$params = $view->params;
$validate = (bool)$view->hp;
?>
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
    <div class="main-contents-body top-original-list navigation-tag-list">

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

                <?php if(($validate)):?>

                <div class="item">
                    <a href="<?php echo $view->params['currentUrl'];?>&display=1" class="btn btn-t-gray read-setting-glonavi">
                        <?php echo $text->get('global_navigation.read_menu_name');?>
                    </a>
                </div>

                <?php endif;?>

            </div>

            <div class="right">
                <a href="<?php echo $params['backTOP'];?>" class="btn btn-t-gray btn-redirect">
                    <?php echo $text->get('back_to_top_detail');?>
                </a>
            </div>

        </div>

        <div class="clearfix"></div>

        <?php if($validate):?>

        <?php $globalNav = $view->params['max_global_navigation'];?>

        <div class="section">
            <div class="section-title">
                <?php echo $text->get('global_navigation.setting.title');?>
            </div>
            <div class="section-body nav-setting">
                <div class="nav-setting-item">
                    <table class="tb-basic tb-centered tb-bordered">
                        <thead>
                        <tr>
                            <th><?php echo $text->get('global_navigation.setting.column_1');?></th>
                            <th><?php echo $text->get('global_navigation.setting.column_2');?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?php echo $view->form->getSubForm('navigation')->getElement('global_navigation')->getValue();?></td>
                            <td>
                                <form id="navigation" name="navigation" enctype="application/x-www-form-urlencoded" method="post" action="">
                                <?php echo $view->form->getSubForm('navigation')->form('company_id');?>
                                <?php echo $view->form->getSubForm('navigation')->form('hd_id');?>
                                <div class="d-flex">
                                    <div class="d-flex flex-start col-5">
                                        <div class="select-custom select-ct lg w-100">
                                        <?php echo $view->form->getSubForm('navigation')->form('global_navigation'); ?>
                                        <?php $messages = $view->form->getSubForm('navigation')->getElement('global_navigation')->getMessages();?>
                                        <?php foreach ($messages as $error):?>
                                            <p style="color:red;"><?php echo h($error)?></p>
                                        <?php endforeach;?>
                                        <div class="sel-custom"><?php echo $view->form->getSubForm('navigation')->getElement('global_navigation')->getValue();?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-end col-5 mr-5">
                                        <input type="submit" name="navigation[submit]" id="global_navigation_select_submit" value="<?php echo $text->get('submit_text'); ?>" class="btn-t-blue">
                                        <?php //echo $view->form->navigation->submit; ?>
                                    </div>
                                </div>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="nav-setting-item">
                    <a href="<?php echo $view->params['currentUrl'];?>" class="btn btn-t-gray read-glonavi">
                        <?php echo $text->get('global_navigation.setting.read_glonavi');?>
                    </a>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="section">
            <div class="section-title">
                <?php echo $text->get('global_navigation.glonavi_title_pc');?>
            </div>
            <div class="section-body">
                <table class="tb-basic tb-centered tb-body-bordered tb-gnav-tags tags-font">
                <thead>
                <tr>
                    <th width="10%"></th>
                    <?php for($i=1;$i<=$globalNav;$i++): ?>
                    <th >グロナビ<?php echo $i;?></th>
                    <?php endfor;?>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>URL</td>
                        <?php for($i=1;$i<=$globalNav;$i++){ ?>
                            <td>
                                <?php if(isset($view->gNav[$i-1])) : ?>
                                <?php $alias = $view->tags_nav->glonavi_url . $i ; ;?>
                                <input type="hidden" value="{<?php echo $alias;?>}" />
                                <span><?php echo $alias;?></span>
                                <span class="copy-icon"></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php } ?>
                    </tr>

                    <tr>
                        <td>ラベル名</td>
                        <?php for($i=1;$i<=$globalNav;$i++): ?>
                            <td>
                                <?php if(isset($view->gNav[$i-1])) : ?>
                                    <?php $alias = $view->tags_nav->glonavi_label . $i ; ;?>
                                    <input type="hidden" value="{<?php echo $alias;?>}" />
                                    <span><?php echo $alias;?></span>
                                    <span class="copy-icon"></span>
                                <?php  else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>

                    <tr>
                        <td>実際の表示名</td>
                        <?php for($i=1;$i<=$globalNav;$i++): ?>
                            <td>
                                <?php
                                    echo (isset($view->gNav[$i-1])) ? $view->gNav[$i-1]['title'] : '-';
                                ?>
                            </td>
                        <?php endfor; ?>
                    </tr>

                </tbody>

            </table>
            </div>
        </div>

        <!-- Global navigation sp -->
        <div class="section">
            <div class="section-title">
                <?php echo $text->get('global_navigation.glonavi_title_sp');?>
            </div>
            <div class="section-body">
                <table class="tb-basic tb-th-left tb-sp-global-navi">
                <thead>
                <tr>
                    <th width="30%" class="title">要素名</th>
                    <th>オリジナルタグ</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $view->tags_spglonavi[0]; ?></td>
                        <td>
                            <input type="hidden" value="{<?php echo $view->tags_spglonavi[1];?>}" />
                            <span><?php echo $view->tags_spglonavi[1];;?></span>
                            <span class="copy-icon"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <!-- ボックスエレメントタグ -->
        @include('admin::company.navigation-tag-partials.item-box', [
                'tags' => $view->tags['tag_element'],
                'di'    =>$view->di
            ])

        <!--$tagsObject->getTags('parts_element')-->
        @include('admin::company.navigation-tag-partials.site', [
                'tags' => $view->tags['tag_site'],
            ])


        <!--広告タグ-->
        @include('admin::company.navigation-tag-partials.html', [
                'tags' => $view->tags['tag_component'],
            ])

        <!--Koma--->
        @include('admin::company.navigation-tag-partials.property', [
                'tags' => $view->tags['tag_property'],
                'params' => $view->params
            ])

        <!--notification-->
        @include('admin::company.navigation-tag-partials.news', [
                'tags' => $view->tags['tag_news'],
            ])

        <!--NHP-3751:フリーワード検索タグ-->
        @include('admin::company.navigation-tag-partials.original-freeword', [
                'submit' => $text->get('submit_text'),
                'ptags' => $view->tags['tag_freeword_parts'],
                'freeword_setting' => $view->form->getSubForm('freeword_setting'),
                'freeword_type_list' => $view->freeword_type_list
            ])

        <?php endif;?>
    </div>
</div>

<?php
echo '<script type="text/javascript" src="/js/admin/global_navigation.js"></script>';
echo '<script type="text/javascript" src="/js/admin/freeword_setting.js"></script>';
?>
@stop