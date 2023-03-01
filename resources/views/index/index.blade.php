@extends('layouts.default')

@section('style')
<link href="/js/libs/jqplot/jquery.jqplot.min.css" media="screen" rel="stylesheet" type="text/css">
@endsection
@section('script')
<!--[if lt IE 9]>
<script type="text/javascript" src="/js/libs/excanvas.min.js"></script>
        <![endif]-->
<script type="text/javascript" src="/js/libs/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.barRenderer.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.pointLabels.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.categoryAxisRenderer.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.canvasOverlay.min.js"></script>
<script type="text/javascript" src="/js/libs/excanvas.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="/js/libs/jqplot/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="/js/access-index-chart.js"></script>
<script type="text/javascript" src="/js/pv-index-chart-home.js"></script>
@endsection

@section('content')
<!-- メインコンテンツ -->
<?php
// $acl = $view->acl();
$agency = !getInstanceUser('cms')->isNerfedTop();
?>

<?php if (isset($view->capacity) && $view->capacity >= config('constants.hp.SITE_OBER_CAPASITY_DATAMAX')) : ?>
    <div class="alert-strong">
        現在使用している容量が一杯なためページ作成や画像の追加が出来ません。<br /><br />
        画像フォルダから不要な画像を削除し容量を確保してください。　　画像削除は<a href="<?php echo route('default.sitesetting.image') ?>">コチラ</a><br />
    </div>
<?php endif; ?>

<div class="main-contents contents-header">
    <div class="section info">
        <h2>アットホームからのお知らせ
            <a href="/information" class="i-s-link">お知らせをすべて見る</a>
        </h2>
        <?php if (count($view->information) > 0) : ?>
            <div class="info-body">
                <ul>
                    <?php foreach ($view->information as $key => $val) : ?>
                        <li>
                            <div class="info-l">
                                <span><?php echo date('Y年m月d日', strtotime($val['start_date'])); ?></span>
                            </div>
                            <div class="info-r">
                                <a href="/information/detail/?id=<?php echo h($val['id']); ?>"> <?php echo h($val['title']); ?></a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else : ?>
            <div class="info-body is-empty">
                <p>現在お知らせはありません。</p>
            </div>
        <?php endif; ?>

    </div>
    <div class="section qr">
        <?php $controller = 'creator' ?>
        <img width="98" src="/image/company-qr" alt="QRコード"><br />
        <?php $siteUrl = getInstanceUser('cms')->getProfile()->getSiteUrl() ?>
        <a target="_blank" href="{{h($siteUrl)}}">本番サイト{{h($siteUrl)}}</a>
        <?php if ($view->acl()->isAllowed('publish', $controller) && $view->acl()->isAllowed('copy-to-company', $controller) && $view->acl()->isAllowed('rollback', $controller) && $view->acl()->isAllowed('delete-hp', $controller)) : ?>
            <?php $siteDomain = getInstanceUser('cms')->getProfile()->getSiteDomain() ?>
            <a target="_blank" href="http://substitute.{{h($siteDomain)}}">代行サイトhttp://substitute.{{h($siteDomain)}}</a>
        <?php else : ?>
            <?php $siteDomain = getInstanceUser('cms')->getProfile()->getSiteDomain() ?>
            <a target="_blank" href="http://test.{{h($siteDomain)}}">テストサイトhttp://test.{{h($siteDomain)}}</a>
        <?php endif; ?>
    </div>
</div>

<!-- 機能 -->
<div class="main-contents contents-function">

    <?php $controller = 'site-setting' ?>
    <?php if ($view->acl()->isAllowed('index', $controller) || $view->acl()->isAllowed('design', $controller) || $view->acl()->isAllowed('image', $controller)) : ?>
        <div class="section-wrap setting">
            <div class="section">
                <h2>基本設定</h2>
                <ul>
                    <?php if ($view->acl()->isAllowed('index', $controller)) : ?>
                        <li>
                            <a href="<?php echo $view->route('index', $controller) ?>">
                                <h3>初期設定</h3>
                                <img src="/images/home/basic_01.png" alt="初期設定">
                                <p>サイト名・サイト説明・キーワード等の共通の設定ができます。</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($view->acl()->isAllowed('design', $controller) && $agency) : ?>
                        <li>
                            <a href="<?php  echo $view->route('design', $controller) ?>">
                                <h3>デザイン選択</h3>
                                <img src="/images/home/basic_02.png" alt="デザイン選択">
                                <p>サイトのテーマ・カラー・レイアウトの設定ができます。</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($view->acl()->isAllowed('image', $controller)) : ?>
                        <li>
                            <a href="<?php echo $view->route('image', $controller) ?>">
                                <h3>画像フォルダ</h3>
                                <img src="/images/home/basic_03.png" alt="画像フォルダ">
                                <p>サイトで使用する画像の管理ができます。</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($view->acl()->isAllowed('file2', $controller)) : ?>
                        <li>
                            <a href="<?php echo $view->route('file2', $controller) ?>">
                                <h3>ファイル管理</h3>
                                <img src="/images/home/basic_10.png" alt="ファイル管理">
                                <p>サイトで使用するファイルの管理ができます。</p>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($view->cms_plan > config('constants.cms_plan.CMS_PLAN_LITE')) : ?>
                        <?php $eSearchSetting = 'estate-search-setting' ?>
                        <?php $eSpecial = 'estate-special' ?>
                        <?php $secondEstate = 'second-estate-search-setting' ?>
                        <?php $view->acl()->isAllowedSecondEstate = getInstanceUser('cms')->isAvailableSecondEstate() && $view->acl()->isAllowed('index', $secondEstate) ?>

                        <?php if ($view->acl()->isAllowed('index', $eSearchSetting)) : ?>
                            <li>
                                <a href="<?php echo $view->route('index', $eSearchSetting)  ?>">
                                    <h3>物件検索設定</h3>
                                    <img src="/images/home/basic_07.png" alt="物件検索設定">
                                    <p>サイトに公開する物件の種目、探し方の設定ができます。</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowedSecondEstate) : ?>
                            <li>
                                <a href="<?php echo $view->route('index', $secondEstate) ?>">
                                    <h3>2次広告自動公開設定</h3>
                                    <img src="/images/home/basic_09.png" alt="2次広告自動公開設定">
                                    <p>2次広告自動公開の対象物件を絞り込む設定ができます。</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowed('index', $eSpecial)) : ?>
                            <li>
                                <a href="<?php echo $view->route('index', $eSpecial)  ?>">
                                    <h3>特集設定</h3>
                                    <img src="/images/home/basic_08.png" alt="特集設定">
                                    <p>特定の条件に当てはまる物件のみを表示させる物件特集の設定ができます。</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php $controller = 'site-map' ?>
    <?php if ($view->acl()->isAllowed('index', $controller)) : ?>
        <div class="section-wrap page">
            <div class="section">
                <h2>ページの作成/更新</h2>
                <ul>
                    <li>
                        <a <?php if (!getInstanceUser('cms')->isCreator() || !getInstanceUser('cms')->getBackupHp()) : ?>href="<?php echo $view->route('index', 'site-map') ?>" <?php else : ?>class="is-disable" <?php endif; ?>>
                            <h3>ページの作成/更新</h3>
                            <img src="/images/home/basic_04.png" alt="初期設定">
                            <p>サイトのメニュー設定・各種ページの作成/更新ができます。</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php $controller = 'publish' ?>
    <?php if ($view->acl()->isAllowed('simple', $controller) && !$view->acl()->isAllowed('delete-hp', 'creator'))/*代行作成は非表示*/ : ?>
        <div class="section-wrap publish">
            <div class="section">
                <h2>サイトの公開/更新</h2>
                <ul>
                    <li class="is-alert">
                        <a href="<?php echo $view->route('simple', $controller) ?>">
                            <h3>公開設定</h3>
                            <img src="/images/home/basic_05.png" alt="公開設定">
                            <p>本番サイトおよびテストサイトへの各ページの公開・非公開を設定できます。</p>
                        </a>
                        <?php if (getInstanceUser('cms')->hasChanged()) : ?>
                            <div class="alert">
                                <img src="/images/home/home_alert.png" alt="">
                                <p>Check</p>
                                <div class="alert-box">
                                    <p>本番に未反映の修正があります。<br>
                                        公開設定から本番反映してください。</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php $controller = 'creator' ?>
    <?php if ($view->acl()->isAllowed('publish', $controller) || $view->acl()->isAllowed('copy-to-company', $controller) || $view->acl()->isAllowed('rollback', $controller) || $view->acl()->isAllowed('delete-hp', $controller) || $view->acl()->isAllowed('publish', $controller)) : ?>
        <div class="section-wrap deptize">
            <div class="section">
                <h2><?php echo ($agency) ? "制作代行" : "代行作成"; ?></h2>
                <div class="deptize-link">
                    <?php if ($view->acl()->isAllowed('publish', $controller)) : ?>
                        <a <?php if (!getInstanceUser('cms')->getBackupHp()) : ?>href="<?php echo $view->route('publish', $controller)  ?>" <?php endif; ?> class="i-s-link<?php if (getInstanceUser('cms')->getBackupHp()) : ?> is-disable<?php endif; ?>"><?php echo ($agency) ? "制作代行テストサイト" : "代行作成テストサイト"; ?></a>
                    <?php endif; ?>
                    <?php if ($view->acl()->isAllowed('copy-to-company', $controller)) : ?>
                        <a <?php if (!getInstanceUser('cms')->getBackupHp()) :?> href="<?php echo $view->route('copy-to-company', $controller) ?>" <?php endif; ?> class="i-s-link<?php if (getInstanceUser('cms')->getBackupHp()) : ?> is-disable<?php endif; ?>">代行更新</a>
                    <?php endif; ?>
                    <?php if ($view->acl()->isAllowed('rollback', $controller)) : ?>
                        <a <?php if (getInstanceUser('cms')->getBackupHp()) : ?>href="<?php echo $view->route('rollback', $controller) ?>" <?php endif; ?> class="i-s-link<?php if (!getInstanceUser('cms')->getBackupHp()) : ?> is-disable<?php endif; ?>">ロールバック</a>
                    <?php endif; ?>
                    <?php if ($view->acl()->isAllowed('delete-hp', $controller)) : ?>
                        <a href="<?php  echo $view->route('delete-hp', $controller) ?>" class="i-s-link"><?php echo ($agency) ? "制作代行サイト削除" : "代行作成サイト削除"; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>


</div>
<!-- /機能 -->
<!-- 評価・分析　-->
<div class="main-contents contents-evaluate diagnosis">
    <div class="section-wrap">
    <div class="section">
    <h2>利用状況  総合評価<?php echo $view->toolTip('rating_total')?><button class="btn-t-blue size-s ml20" id="confirm-capacity">CMS使用容量を確認する</button></h2>
        <a href="<?php echo $view->route('rating', 'diacrisis') ?>" class="i-s-link">すべてを見る</a>

        <div class="graph-area" style="width:550px;">
            <?php echo $view->pvPointChart($view->pv, $view->total_points, $view->max_point) ?>
        </div>

        <!-- 指標 -->
        <div class="page-area column3">
            <div class="col">
                <div class="page-element">
                    <div class="page-element-header">
                        <h3>更新<?php echo $view->toolTip('rating_update')?></h3>
                    </div>

                    <div class="page-element-body">
                        <div class="page-element-body-in">
                            <div class="diagnosis-meter-img">
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
                            <div class="diagnosis-meter-img">
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
                            <div class="diagnosis-meter-img">
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
    </div>


    <div class="section-wrap">
    <div class="section">
        <h2>アクセス状況  総合評価<?php echo $view->toolTip('analysis_total')?></h2>
        <a href="<?php echo $view->route('analysis', 'diacrisis') ?>" class="i-s-link">すべてを見る</a>

        <div class="graph-area" id="access-contact-graph" ></div>
        <div style="display:none" id="graph-area-data-upv" ><?php echo(json_encode($view->uniquePageViews));?></div>
        <div style="display:none" id="graph-area-data-ccnt" ><?php echo(json_encode($view->contactCount));?></div>
        <div style="display:none" id="graph-area-data-date" ><?php echo(json_encode($view->getDateList));?></div>

        <!-- 指標 -->
        <div class="analytics-summary">

            <table class="tb-basic">
                <thead>
                    <tr>
                        <th colspan="2">アクセス数（前月比）</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>PV数</th>
                        <td class="td-comparison">
                            <?php  $data=$view->pageviews; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>セッション数</th>
                        <td class="td-comparison">
                            <?php  $data=$view->visits; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>ユーザー数</th>
                        <td class="td-comparison">
                            <?php  $data=$view->uniquePageviews; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="tb-basic">
                <thead>
                    <tr>
                        <th colspan="2">問い合わせ数（前月比）</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>お問い合わせ</th>
                        <td class="td-comparison">
                            <?php  $data=$view->generalContactCount; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>資料請求</th>
                        <td class="td-comparison">
                            <?php  $data=$view->assesmentContactCount; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>売却査定</th>
                        <td class="td-comparison">
                            <?php  $data=$view->documentContactCount; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="tb-basic">
                <thead>
                    <tr>
                        <th colspan="2">物件リクエスト（前月比）</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>居住用賃貸</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForRequestLivingLease	; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>事業用賃貸</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForRequestOfficeLease	; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>居住用売買</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForRequestLivingBuy		; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>事業用売買</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForRequestOfficeBuy		; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="tb-basic">
                <thead>
                    <tr>
                        <th colspan="2">物件お問い合わせ（前月比）</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>居住用賃貸</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForLivingLease	; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>事業用賃貸</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForOfficeLease	; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>居住用売買</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForLivingBuy		; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>

                    <tr>
                        <th>事業用売買</th>
                        <td class="td-comparison">
                            <?php $data = $view->estateContactCountForOfficeBuy		; ?>
                            <p><?php echo $data['base-month-val'] ?>件</p>
                            <span class="<?php echo $data['prev-month-gap-dirct']?>"><?php echo $data['prev-month-gap'] ?>件</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- /指標 -->
        <?php if ( $view->cms_plan <= config('constants.cms_plan.CMS_PLAN_STANDARD') ) : ?>
            <p>※「資料請求」「売却査定」「物件リクエスト」はアドバンスご契約の場合にのみご利用いただけます。</p>
        <?php endif ; ?>
        <?php if ( $view->cms_plan < config('constants.cms_plan.CMS_PLAN_STANDARD')) : ?>
            <p>※「物件お問い合わせ」はアドバンスまたはスタンダードご契約の場合にのみご利用いただけます。</p>
        <?php endif ; ?>
    </div>
    </div>

</div>
<!-- /評価・分析　-->

@endsection