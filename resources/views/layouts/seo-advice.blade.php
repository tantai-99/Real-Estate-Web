<!DOCTYPE html>
<html lang="ja">
<head>
    @include('layouts.partials.head')
</head>
<body class="seo">
<!-- contents -->
<div id="main">
    <!-- 簡易設定 -->
    <div class="main-contents">
        <div class="main-contents-body">
            @yield('content')
            <div class="section">
                <div class="seo-pagetop">
                    <a href="javascript:app.scrollTo(0)">ページの上部へ戻る</a>
                </div>
            </div>

            <div id="g-footer">
                <small>Copyright(C)At Home Co., Ltd.</small>
                <a href="" onclick="window.close();">このウインドウを閉じる</a>
            </div>
        </div>
    </div>
</div>
<!-- /contents -->

</body>
</html>