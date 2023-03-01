@extends('layouts.default')

@section('title', __('公開設定(簡易設定)'))

@section('style')
<link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css">
<style>
    .populate-testsite {
    width: 760px;
    margin: 0 auto;
    }
    .must-publish {
    color: #00b1c6;
    }
    .td-check dt {
    display: none;
    }
</style>
@endsection
@section('script')
<script type="text/javascript" src="/js/libs/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="/js/publish.js"></script>
<script type="text/javascript">
var $exclusive_error_msg = "<?php echo str_replace("\n", "\\n", getConfigs('publish')->publish->exclusive_error_msg);?>";
</script>
@endsection

@section('content')
<div class="main-contents publish">
    <h1>
        公開設定<span>(簡易設定)</span><a href="javascript:void(0)" class="btn-t-gray">詳細設定</a>
    </h1>
    <div class="main-contents-body">
        <div class="section">
            <?php if($view->displayEstateRequestFlg) : ?>
                <div class="alert-strong">
                    <span class="request_top_error">
                            ・「物件検索設定」にて「物件リクエストを利用する」を選択しています。<br>
                            　「ページの作成/更新」にて該当の物件種別の「物件リクエスト」フォームを作成してください。
                            </span>
                    <input type="hidden" id="no_request_flg" value="1">
                </div>
            <?php endif; ?>
            <!--初期設定・デザイン・物件検索設定の反映 -->
            <?php if ($view->hp->all_upload_flg) : ?>
                <p class="table-heading">
                    共通設定の反映
                </p>
                <table class="tb-basic publish-easy"<?php if ($view->displayEstateSettingFlg) :?> style="margin-bottom:20px;"<?php endif ;?>>
                    <tbody>
                    <?php $all_upload_parts = json_decode($view->hp->all_upload_parts); ?>
                    <?php if ($all_upload_parts->initial) :?>
                    <tr class="bg">
                        <td>初期設定</td>
                    </tr>
                    <?php endif ;?>
                    <?php if ($all_upload_parts->design) :?>
                    <tr class="bg">
                        <td>デザイン</td>
                    </tr>
                    <?php endif ;?>
                    <?php if ($all_upload_parts->topside) :?>
                    <tr class="bg">
                        <td>トップページ サイドコンテンツ（全ページ共通）</td>
                    </tr>
                    <?php endif ;?>
                </table>
            <?php endif; ?>
            <?php if ($view->displayEstateSettingFlg) : ?>
                <p class="table-heading">
                    物件検索設定の反映
                </p>
                <table class="tb-basic publish-easy">
                    <tbody>
                    <tr class="bg">
                        <td>物件検索設定（物件検索トップ、各物件種目トップ、物件問い合わせ）</td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <form data-api-action="<?php echo $view->route('api-publish', 'publish') ?>" method="post" id="publish-form">
                @csrf
                <?php echo $view->form->form('submit_from'); ?>
                <?php if ($view->hp->all_upload_flg) : ?>
                    <div class="publish-btn">
                        <?php if (!$view->subsutitute) : ?>
                            <a href="javascript:void(0);" class="btn-t-blue size-l submit-allupload-testsite">テストサイトの更新処理に進む</a>
                        <?php endif; ?>
                        <a href="javascript:void(0);" class="btn-t-blue size-l <?php if (!$view->subsutitute) : ?>submit-allupload<?php else : ?>submit-allupload-subsutitute<?php endif; ?>">
                            <?php if (!$view->subsutitute) : ?>本番サイト<?php else : ?>代行作成テストサイト<?php endif; ?>の公開/更新
                        </a>
                    </div>
                <?php else : ?>
                    <div class="publish-btn">
                        <?php if (!$view->subsutitute) : ?>
                            <a href="javascript:void(0);" class="btn-t-blue size-l submit-testsite">テストサイトの更新処理に進む</a>
                        <?php endif; ?>
                        <a href="javascript:void(0);" class="btn-t-blue size-l submit-<?php if (!$view->subsutitute) : ?>publish<?php else : ?>publish-subsutitute<?php endif; ?>">
                            <?php if (!$view->subsutitute) : ?>本番サイト<?php else : ?>代行作成テストサイト<?php endif; ?>の公開/更新
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!$view->subsutitute) : ?>
                    <div class="alert-normal">
                        <strong>テストサイトについて</strong><br/>
                        <p>テストサイトとは、本番サイトで公開する前にサイト全体の内容を確認することのできる確認用ホームページとなります。<br/>
                            「テストサイトの更新処理に進む」をクリックすると、テストサイトの公開・更新ができます。<br/>
                            テストサイトの閲覧にはID,パスワードが必要です。<br/>
                            IDは本ツールのユーザーIDと同じです。パスワードは初期設定で設定したパスワードです。パスワードは初期設定で変更できます。<br/><br/>
                            <?php $siteDomain = getInstanceUser('cms')->getProfile()->getSiteDomain() ?>
                            テストサイト：<a target="_blank" href="http://test.<?php echo h($siteDomain) ?>">http://test.<?php echo h($siteDomain) ?></a>
                        </p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
@endsection
