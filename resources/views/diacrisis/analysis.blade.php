@extends('layouts.default')
@section('title') 評価・分析 @stop
@section('content') 
        @section('script')
        <!--[if lt IE 9]>
            <script src=('/js/libs/excanvas.min.js')>  </script>
        <![endif]-->
        <script src="/js/libs/jquery.jqplot.min.js"></script> 
        <script src="/js/libs/jqplot/jqplot.donutRenderer.min.js"></script>
        <script src="/js/libs/jqplot/jqplot.barRenderer.min.js"></script>
        <script src="/js/libs/jqplot/jqplot.categoryAxisRenderer.min.js"></script>
        <script src="/js/libs/jqplot/jqplot.categoryAxisRenderer.min.js"></script>
        <script src="/js/libs/jqplot/jqplot.pointLabels.min.js"></script>
        <script src="/js/libs/jqplot/jqplot.pieRenderer.min.js"></script>
        <script src="/js/analysis.js"></script>
        @stop
        @Section('style')
        <link rel="stylesheet" href="/js/libs/jqplot/jquery.jqplot.min.css">
        @stop
<style>
  .jqplot-donut-series {
    color:#fff;
  }
</style>
<style type="text/css">
.analytics-graph-set .jqplot-data-label {
	color:#666;
	font-size:12px;
}

</style>
		<input type="hidden" id="domain" value="<?php echo $view->company->domain;?>">
		<!-- ツール評価 -->
		<div class="main-contents diagnosis">
			<h1>評価・分析</h1>
			<div class="main-contents-body">
            <?php if(!$view->hasValidCompany):?>
            <div class="alert-strong">有効な子会社がありません。</div>
            <input type="hidden" name="has_analytics_tag" value="0" id="has_analytics_tag">
            <?php else:?>

				<?php if(!$view->hasAnalyticsTag):?>
				<div class="alert-strong">システムエラー<br/>弊社営業担当までお問い合わせください</div>
				<?php endif;?>
                <div class="diagnosis-header">
                    <h3><?php echo h($view->compnay->member_name) ?></h3>
                    <div <?php if (!$view->companySelectForm->hasMultiCompanies()): ?>style="display:none"<?php endif ?>>会社を選択
                        <form method="GET" style="display: inline;">
                            <?php echo $view->companySelectForm->form('company_id') ?>
                            <input type="submit" class="btn-t-blue size-s" value="切替"/>
                        </form>
                    </div>
                </div>
				<div class="m-tab">
                    <a href="rating">ツール評価</a>
                    <a href="analysis" class="is-active">アクセスログ</a>
                </div>


                 <div class="section analytics-summary">
                    <h2>サマリー</h2>
                    <div class="analyticts-date">
                        <select name="" id="summary-year">
                            <?php foreach($view->yearOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>年</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="" id="summary-month">
                            <?php foreach($view->monthOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>月</option>
                            <?php endforeach; ?>                            
                        </select>
                        <span id="summary-base-period">当月期間：</span>
                    </div>

                    <table class="tb-basic" id="summary-table">
                        <thead>
                            <tr>
                                <th>項目名</th>
                                <th class="is-current">当月</th>
                                <th colspan="2">前月(前月比)</th>
                                <th colspan="2">前年当月(前年当月比)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>セッション数</th>
                                <td class="alR is-current" id="name"> </td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                            </tr>

                            <tr>
                                <th>新規ユーザー数</th>
                                <td class="alR is-current"> </td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                            </tr>

                            <tr>
                                <th>ユーザー数</th>
                                <td class="alR is-current"> </td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                            </tr>

                            <tr>
                                <th>ページビュー数</th>
                                <td class="alR is-current"> </td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                            </tr>

                            <tr>
                                <th>ページ/セッション</th>
                                <td class="alR is-current"> </td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                            </tr>

                            <tr>
                                <th>直帰率</th>
                                <td class="alR is-current"> </td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                            </tr>

                            <tr class="reverse">
                                <th>問い合わせ件数 <?php echo $view->toolTip('access_log_inquiry');?></th>
                                <td class="alR is-current"> </td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                                <td class="alR"> </td>
                                <td class="td-comparison"><span class="is-none"> </span></td>
                            </tr>

                        </tbody>
                    </table>
                </div>


                 <div class="section analytics-access">
                    <h2>アクセス状況 推移</h2>

                    <div class="analyticts-date">
                        <select name="" id="access-year">
                            <?php foreach($view->yearOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>年</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="" id="access-month">
                            <?php foreach($view->monthOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>月</option>
                            <?php endforeach; ?>
                        </select>
                        <span id="access-base-period">当月期間：</span>
                    </div>

                    <table class="tb-basic"  id="access-table">
                        <thead>
                            <tr>
                                <th>項目名</th>
                                <th> </th>
                                <th> </th>
                                <th> </th>
                                <th> </th>
                                <th> </th>
                                <th class="is-current"> </th>
                                <th class="is-average">期間平均</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>セッション数</th>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                                <td> </td>
                            </tr>

                            <tr>
                                <th>新規ユーザー数</th>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                                <td> </td>
                            </tr>

                            <tr>
                                <th>ユーザー数</th>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                                <td> </td>
                            </tr>

                            <tr>
                                <th>ページビュー数</th>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                                <td> </td>
                            </tr>

                            <tr>
                                <th>ページ/セッション</th>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                                <td> </td>
                            </tr>

                            <tr>
                                <th>直帰率</th>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                                <td> </td>
                            </tr>

                            <tr>
                                <th>問い合わせ件数 <?php echo $view->toolTip('access_log_inquiry');?></th>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                                <td> </td>
                            </tr>

                        </tbody>
                    </table>
                    <div class="analytics-graph" id="access-graph"></div>
                </div>

                 <div class="section analytics-device">
                    <h2>デバイス別　アクセス情報</h2>

                    <div class="analyticts-date">
                        <select name="" id="access-device-year">
                            <?php foreach($view->yearOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>年</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="" id="access-device-month">
                            <?php foreach($view->monthOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>月</option>
                            <?php endforeach; ?>
                        </select>
                        <span id="access-device-base-period">当月期間：</span>
                    </div>

                    <div class="analytics-graph-set">
                        <div class="analytics-graph" id="access-device-visit-graph"></div>
                        <div class="analytics-graph" id="access-device-page-visit-graph"></div>
                        <div class="analytics-graph" id="access-device-bounces-rate-graph"></div>
                    </div>
                </div>

                 <div class="section analytics-media">
                    <h2>メディア別データ</h2>

                    <div class="analyticts-date">
                        <select name="" id="access-media-year">
                            <?php foreach($view->yearOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>年</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="" id="access-media-month">
                            <?php foreach($view->monthOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>月</option>
                            <?php endforeach; ?>
                        </select>
                        <span id="access-media-base-period">当月期間：</span>
                    </div>

                    <div class="analytics-graph-set">
                        <div class="analytics-graph" id="access-media-graph"></div>
                    </div>


                    <?php //$mediaTables  = array('gorg'=>'google/organic','gcpc'=>'google/cpc','yorg'=>'yahoo/organic','ycpc'=>'yahoo/cpc')  ?>
                    <?php $mediaTables  = array('cpc'=>'cpc/banner ＜広告経由での流入＞', 'org'=>'organic ＜検索エンジン（Yahoo・Google等）経由での流入＞','ref'=>'referral ＜ポータルサイトやブログなど、Webサイト経由での流入＞','(none)'=>'(none) ＜URL（アドレス）の直接入力やお気に入り経由などでの流入＞')  ?>
                    <?php foreach($mediaTables as $key=>$name): ?>
                    <h3><?php echo $name ?></h3>
                    <table class="tb-basic" id="access-media-table-<?php echo $key ?>">
                        <thead>
                            <tr>
                                <th>項目名</th>
                                <th>2014/09</th>
                                <th>2014/10</th>
                                <th>2014/09</th>
                                <th>2014/11</th>
                                <th>2014/12</th>
                                <th class="is-current">2015/01</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>セッション数</td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                            </tr>

                            <tr>
                                <th>新規ユーザー数</td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                            </tr>

                            <tr>
                                <th>ユーザー数</td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                            </tr>

                            <tr>
                                <th>ページビュー数</td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                            </tr>

                            <tr>
                                <th>ページ/セッション</td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                            </tr>

                            <tr>
                                <th>直帰率</td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td class="is-current"> </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endforeach; ?>
                </div>

<?php /* ?>
                 <div class="section analytics-ranking">
                    <h2>月間キーワードTOP20</h2>

                    <div class="analyticts-date">
                        <select name="" id="access-keyword-ranking-year">
                            <?php foreach($view->yearOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>年</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="" id="access-keyword-ranking-month">
                            <?php foreach($view->monthOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>月</option>
                            <?php endforeach; ?>
                        </select>
                        <span id="access-keyword-ranking-base-period">当月期間</span>
                    </div>

                    <table class="tb-basic" id="access-keyword-ranking-table">
                        <thead>
                            <tr>
                                <th>順位</th>
                                <th>キーワード</th>
                                <th>セッション数</th>
                                <th>新規ユーザー数</th>
                                <th>ユーザー数</th>
                                <th>ページビュー数</th>
                                <th>ページ/セッション</th>
                                <th>直帰率</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($rank=1; $rank<=20; $rank++ ): ?>
                            <tr>
                                <th><?php echo $rank ?></td>
                                <td class="alL"> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
<?php */ ?>

                <div class="section analytics-ranking">
                    <h2>ページ別 セッション数 TOP20</h2>

                    <div class="analyticts-date">
                        <select name="" id="access-page-ranking-year">
                            <?php foreach($view->yearOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>年</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="" id="access-page-ranking-month">
                            <?php foreach($view->monthOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>月</option>
                            <?php endforeach; ?>
                        </select>
                        <span id="access-page-ranking-base-period">当月期間：</span>
                    </div>

                    <table class="tb-basic"  id="access-page-ranking-table">
                        <thead>
                            <tr>
                                <th>順位</th>
                                <th class="cell2">ページ名</th>
                                <th>セッション数</th>
                                <th>新規ユーザー数</th>
                                <th>ユーザー数</th>
                                <th>ページビュー数</th>
                                <th>ページ/セッション</th>
                                <th>直帰率</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($rank=1; $rank<=20; $rank++ ): ?>
                            <tr>
                                <th><?php echo $rank ?></td>
                                <td class="alL"> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <div class="section analytics-ranking">
                    <h2>ページ別 ページビュー数 TOP20</h2>

                    <div class="analyticts-date">
                        <select name="" id="access-page-view-year">
                            <?php foreach($view->yearOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>年</option>
                            <?php endforeach; ?>
                        </select>
                        <select name="" id="access-page-view-month">
                            <?php foreach($view->monthOptions as $option) : ?>
                            <option value="<?php echo $option ?>"><?php echo $option ?>月</option>
                            <?php endforeach; ?>
                        </select>
                        <span id="access-page-view-base-period">当月期間：</span>
                    </div>

                    <table class="tb-basic"  id="access-page-view-table">
                        <thead>
                            <tr>
                                <th>順位</th>
                                <th class="cell2">ページ名</th>
                                <th>ページビュー数</th>
                                <th>ページ別セッション数</th>
                                <th>平均滞在時間</th>
                                <th>離脱率</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($rank=1; $rank<=20; $rank++ ): ?>
                            <tr>
                                <th><?php echo $rank ?></td>
                                <td class="alL"> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

			</div>
            <input type="hidden" name="company_id" value="<?php echo $view->compnay->id ?>" id="company_id">
            <input type="hidden" name="has_analytics_tag" value="<?php echo $view->hasAnalyticsTag ?>" id="has_analytics_tag">
        <?php endif;?>
		</div>
		<!-- /ツール評価 -->

@endsection