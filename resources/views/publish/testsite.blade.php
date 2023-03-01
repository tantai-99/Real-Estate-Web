@extends('layouts.default')

@section('title', __('テストサイト'))
@section('style')
<link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css">
<style>
    #publish-form table tbody tr td dt{
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
<div class="main-contents testsite">
    <h1>テストサイト確認</h1>

    <div class="main-contents-body">
        <div class="errors" id="error-top"></div>
        <form data-api-action="<?php echo route('default.publish.api_publish'); ?>" action="<?php echo route('default.publish.api_publish'); ?>" method="post" id="publish-form">
            @csrf

            <div class="section test-btn">
                <?php if (count($view->pages) > 0) : ?>
                    <p>現在時刻で確認する</p>
                <?php endif ;?>
                <div class="btn-area">
                    <a href="javascript:void(0);" class="btn-t-blue size-l submit-publish-testsite-now">公開/更新</a>
                </div>
            </div>

            <?php if (count($view->pages) > 0) : ?>
                <div class="section">
                    <p>予約日時で確認する</p>
                    <table class="tb-basic test-check">
                        <thead>
                        <tr>
                            <th></th>
                            <th>予約日時</th>
                            <th>ページ</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $cnt = 0; ?>
                        <?php foreach ($view->pages as $releaseAt => $list) : ?>
                            <tr <?php if ($cnt % 2 === 1) : ?>class="bg"<?php endif; ?>>
                                <td><?php echo($view->form->form('releaseAt'.++$cnt)); ?></td>
                                <td><?php //echo date$releaseAt)->toString('YYYY年MM月dd日HH時'); ?></td>
                                <td class="test-page">
                                    <?php foreach ($list as $releaseType => $pages) : ?>
                                        <div>
                                            <strong>
                                                <?php if ($releaseType == config('constants.release_schedule.RESERVE_RELEASE')) : ?>
                                                    公開開始
                                                <?php elseif ($releaseType == config('constants.release_schedule.RESERVE_CLOSE')) : ?>
                                                    公開停止
                                                <?php endif; ?>
                                            </strong>
                                            <div>
                                                <?php foreach ($pages as $title) : ?>
                                                    <span><?php echo $title; ?></span>
                                                <?php endforeach; ?>

                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="btn-area">
                        <a href="javascript:void(0);" class="btn-t-blue size-l submit-publish-testsite-reserve">公開/更新</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="alert-normal">
                <strong>テストサイトについて</strong>
                <p>テストサイトのIDは、本ツールのユーザIDと同じです。テストサイトのパスワードは、初期設定で設定したパスワードです。パスワードは初期設定で変更できます。</p>
            </div>

        </form>
    </div>
</div>
@endsection