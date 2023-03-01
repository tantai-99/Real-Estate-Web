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

			<?php if( $view->displayEstateRequestFlg ) : ?>
				<div class="alert-strong">
					「物件検索設定」にて「物件リクエストを利用する」を選択しています。<br>
					「ページの作成/更新」にて該当の物件種別の「物件リクエスト」フォームを作成してください。
					<input type="hidden" id="no_request_flg" value="1">
				</div>
			<?php endif; ?>

            <?php if ($view->hp->all_upload_flg || $view->pages) : ?>
                <?php if ($view->pages): ?>
                    <div class="alert-strong">以下のページが本番に未反映です。</div>
                    <?php if ($view->hasAutoUpdatePage && !$view->hp->all_upload_flg) : ?>
                    <div class="alert-strong">「※自動更新」と表示されたページは公開が必要となるため、チェックを外せません。</div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($view->hp->all_upload_flg) : ?>
                    <div class="alert-strong">公開中のすべてのページに「共通設定」の変更を反映する必要があるため、公開中のページはチェックを外せません。</div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($view->hasPrereserved) : ?>
                <div class="populate-testsite">
                    <a class="i-s-link" href="/publish/detail?testsite=1">前回テストサイト更新時と同じ設定にする（詳細設定に移動します）</a>
                </div>
            <?php endif; ?>

            <div class="errors" id="error-top"></div>

                <p>本番に未反映のページはありません</p>
        </div>
    </div>
</div>
@endsection
