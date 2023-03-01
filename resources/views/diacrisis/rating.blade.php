<?php
use Illuminate\Support\Facades\App;
use App\Repositories\HpPage\HpPageRepositoryInterface;
?>
@extends('layouts.default')

@section('title') 評価・分析 @stop

@section('style')
<link href="/js/libs/jqplot/jquery.jqplot.min.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript" src="/js/libs/excanvas.min.js"></script>
<script type="text/javascript" src="/js/libs/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.canvasOverlay.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="/js/pv-index-chart-home.js"></script>
@endsection

@section('content')
<!-- contents -->
    <!-- ツール評価 -->
    <div class="main-contents diagnosis">
    <h1>評価・分析<button class="btn-t-blue size-s" style="float: right;" id="confirm-capacity">CMS使用容量を確認する</button></h1>

    <div class="main-contents-body">
    <?php if(!$view->hasValidCompany):?>
        <div class="alert-strong">有効な会社がありません。</div>
    <?php else:?>

    <div class="diagnosis-header">
        <h3>
            <?php echo $view->company->member_name ?>
        </h3>

        <?php if ($view->companySelectForm->hasMultiCompanies()): ?>
            <div>
                会社を選択
                <form method="GET" style="display: inline;">
                    <?php echo $view->companySelectForm->form('company_id') ?>
                    <input type="submit" class="btn-t-blue size-s" value="切替"/>
                </form>
            </div>
        <?php endif ?>
    </div>

    <div class="m-tab">
        <a href="rating" class="is-active">ツール評価</a>
        <a href="analysis">アクセスログ</a>
    </div>


    <div class="section">
        <!-- 総合評価 -->
        <div class="page-area">
            <div class="page-element element-text">
                <div class="page-element-header">
                    <h3>総合評価<?php echo $view->toolTip('rating_total')?></h3>
                </div>

                <div class="page-element-body">
                    <div class="page-element-body-in">
                        <?php echo $view->pvPointChart($view->pv, $view->total_points, $view->max_point) ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- /総合評価 -->

        <!-- 指標 -->
        <div class="page-area column3">
            <div class="col">
                <div class="page-element">
                    <div class="page-element-header">
                        <h3>更新<?php echo $view->toolTip('rating_update')?></h3>
                    </div>

                    <div class="page-element-body">
                        <div class="page-element-body-in">
                            <div class="diagnosis-meter-img is-step1">
                                <img src="/images/diagnosis/meter_l_0<?php echo $view->update_point ?>.png" alt="<?php echo $view->update_point ?>">
                            </div>
                            <div class="diagnosis-detail-list">
                                <dl>
                                    <dt>サイト全体</dt>
                                    <dd><?php echo $view->site_published ? date('m月d日', $view->site_published) : '-' ?></dd>
                                </dl>
                                <dl>
                                    <dt>お知らせ</dt>
                                    <dd><?php echo $view->information_published ? date('m月d日', $view->information_published) : '-' ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="page-element">
                    <div class="page-element-header">
                        <h3>ページ作成<?php echo $view->toolTip('rating_page')?></h3>
                    </div>

                    <div class="page-element-body">
                        <div class="page-element-body-in">
                            <div class="diagnosis-meter-img is-step2">
                                <img src="/images/diagnosis/meter_l_0<?php echo $view->adequacy_point ?>.png" alt="<?php echo $view->adequacy_point ?>">
                            </div>
                            <div class="diagnosis-detail-list">
                                <dl>
                                    <dt>公開</dt>
                                    <dd><?php echo $view->adequacy_counts_total['public'] ?>ページ</dd>
                                </dl>
                                <dl>
                                    <dt>下書き</dt>
                                    <dd><?php echo $view->adequacy_counts_total['draft'] ?>ページ</dd>
                                </dl>
                                <dl>
                                    <dt>未作成</dt>
                                    <dd><?php echo $view->adequacy_counts_total['new'] ?>ページ</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="page-element">
                    <div class="page-element-header">
                        <h3>機能設定<?php echo $view->toolTip('rating_function')?></h3>
                    </div>

                    <div class="page-element-body">
                        <div class="page-element-body-in">
                            <div class="diagnosis-meter-img is-step3">
                                <img src="/images/diagnosis/meter_l_0<?php echo $view->utilization_point ?>.png" alt="<?php echo $view->utilization_point ?>">
                            </div>
                            <div class="diagnosis-detail-list">
                                <dl>
                                    <dt>登録</dt>
                                    <dd><?php echo $view->num_utilized ?>項目</dd>
                                </dl>
                                <dl>
                                    <dt>未登録</dt>
                                    <dd><?php echo $view->num_unutilized ?>項目</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- /指標 -->

    </div>

    <!-- ページ作成 -->
    <div class="section">
        <h2>
            ページ作成
            <div class="content-button-open-close">
                <button class="btn-t-open-all btn-t-blue size-s">すべて開く</button>
                <button class="btn-t-close-all is-hide btn-t-blue size-s">すべて閉じる</button>
            </div>
        </h2>

        <?php foreach ($view->category_map as $category_id => $category): ?>
            <?php if ($category_id == config('constants.hp_page.CATEGORY_LINK')) :?>
                <h3 class="page-creation-title"><?php echo $view->category_map_article[config('constants.hp_page.TYPE_USEFUL_REAL_ESTATE_INFORMATION')]['label'] ?></h3>
                <div class="page-area column4 is-hide">
                <?php foreach($view->category_map_article[config('constants.hp_page.TYPE_USEFUL_REAL_ESTATE_INFORMATION')]['pages'] as $large): ?>
                    <h3 class="page-creation-title"><?php echo $view->category_map_article[$large]['label'] ?></h3>
                    <div class="page-area column-child is-hide">
                    <?php foreach($view->category_map_article[$large]['pages'] as $small): ?>
                        <div class="col">
                        <?php $disable = in_array( $small, $view->disable_pages ) ? 'disable-page is-disable' : ''  ; ?>
                        <?php 
                            $disable_standard='';
                            if($view->disable_with_standard_page){
                                $disable_standard = in_array( $small, $view->disable_with_standard_page ) ? ' disable-standard' : ''  ; 
                            }
                        ?>
                        <div class="page-element <?= $disable.$disable_standard ?>">
                            <div class="page-element-header">
                                <h3><?php echo $view->category_map_article[$small]['label'] ?></h3>
                            </div>

                            <div class="page-element-body">
                                <div class="page-element-body-in">
                                    <div class="diagnosis-detail-list">
                                        <dl>
                                            <dt>公開中</dt>
                                            <dd><?php echo $view->article_count[$large][$small]['public']?>ページ</dd>
                                        </dl>
                                        <dl>
                                            <dt>下書き(非公開)</dt>
                                            <dd><?php echo $view->article_count[$large][$small]['draft']?>ページ</dd>
                                        </dl>
                                        <?php if ($small != config('constants.hp_page.TYPE_SMALL_ORIGINAL')):?>
                                        <dl>
                                            <dt>未作成(非公開)</dt>
                                            <dd><?php echo $view->article_count[$large][$small]['new']?>ページ</dd>
                                        </dl>
                                        <?php endif;?>
                                    </div>
                                    <?php if ($view->article_count[$large][$small]['public'] == 0 && $view->article_count[$large][$small]['draft'] == 0): ?>
                                        <div class="diagnosis-alerttext-strong">
                                            ページが作成されていません
                                        </div>
                                    <?php endif ?>
                                    <?php if ($view->article_count[$large][$small]['draft'] > 0): ?>
                                        <div class="diagnosis-alerttext-normal">
                                            下書きのみのページがあります
                                        </div>
                                    <?php endif ?>
                                    <?php if ($view->article_count[$large][$small]['new'] > 0 && ($view->article_count[$large][$small]['draft'] > 0 || $view->article_count[$large][$small]['public'])) : ?>
                                        <div class="diagnosis-alerttext-normal">
                                            未作成のページがあります
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endforeach;?>
                </div>
            <?php endif;?>
            <?php if (App::make(HpPageRepositoryInterface::class)->isOldTemplateArticle($category_id)) continue;?>
            <h3 class="page-creation-title"><?php echo $category['label'] ?></h3>
            <div class="page-area column4 is-hide">
                <?php foreach ($category['pages'] as $page_type): ?>
                <?php if (App::make(HpPageRepositoryInterface::class)->isOldTemplateArticle(null, $page_type)) continue;?>
                <?php if (in_array($page_type, App::make(HpPageRepositoryInterface::class)->getHasDetailPageTypeList())) continue; ?>
                <?php if ($page_type == config('constants.hp_page.TYPE_ESTATE_ALIAS')) continue; ?>
                    <div class="col">
                        <?php $disable = in_array( $page_type, $view->disable_pages ) ? 'disable-page is-disable' : ''  ; ?>
                        <?php 
                            $disable_standard='';
                            if($view->disable_with_standard_page){
                                $disable_standard = in_array( $page_type, $view->disable_with_standard_page ) ? ' disable-standard' : ''  ; 
                            }
                        ?>
                        <div class="page-element <?= $disable.$disable_standard ?>">
                            <div class="page-element-header">
                                <h3><?php echo $view->page_types[$page_type] ?></h3>
                            </div>

                            <div class="page-element-body">
                                <div class="page-element-body-in">
                                    <div class="diagnosis-detail-list">
                                        <dl>
                                            <dt>公開中</dt>
                                            <dd><?php echo $view->adequacy_counts[$page_type]['public'] ?>ページ</dd>
                                        </dl>
                                        <dl>
                                            <dt>下書き(非公開)</dt>
                                            <dd><?php echo $view->adequacy_counts[$page_type]['draft'] ?>ページ</dd>
                                        </dl>
                                    </div>
                                    <?php if ($view->adequacy_counts[$page_type]['public'] == 0 && $view->adequacy_counts[$page_type]['draft'] == 0): ?>
                                        <div class="diagnosis-alerttext-strong">
                                            ページが作成されていません
                                        </div>
                                    <?php endif ?>
                                    <?php if ($view->adequacy_counts[$page_type]['draft'] > 0): ?>
                                        <div class="diagnosis-alerttext-normal">
                                            下書きのみのページがあります
                                        </div>
                                    <?php endif ?>
                                </div>
                                <?php if ($page_type == config('constants.hp_page.TYPE_INFO_DETAIL')): ?>
                                <div class="diagnosis-alerttext-note">
                                    ※お知らせ（一覧のみ追加）は対象に含まれません。
                                </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endforeach ?>


    </div>
    <!-- /ページ作成 -->


    <!-- 機能設定 -->
    <div class="section">
    <h2>
            機能設定
            <div class="content-button-open-close">
                <button class="btn-t-open-all btn-t-blue size-s">開く</button>
                <button class="btn-t-close-all is-hide btn-t-blue size-s">閉じる</button>
            </div>
        </h2>

        <div class="page-area column5 is-hide">
            <?php foreach ($view->utilization_functions as $function_name => $label): ?>
                <?php if ($label == null) continue; ?>
                <div class="col">
                    <div class="page-element">
                        <div class="page-element-header">
                            <h3><?php echo $label ?></h3>
                        </div>

                        <div class="page-element-body">
                            <?php if ($view->utilization[$function_name]): ?>
                                <div class="page-element-body-in">
                                    <p>設定済み</p>
                                </div>
                            <?php else: ?>
                                <div class="page-element-body-in">
                                    <p>未設定</p>

                                    <div class="diagnosis-alerttext-strong">
                                        登録されていません
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>

        </div>

    </div>
    <!-- /機能設定 -->

    <?php endif;?>
    </div>
    </div>
    <!-- /ツール評価 -->
    <!-- /contents -->
@endsection