<!DOCTYPE html>
<html lang="ja">
<head>
    @include('layouts.partials.head')
</head>
<body class="utility">
<!-- contents -->
<div id="main">
    <!-- 簡易設定 -->
    <div class="main-contents">
        <div id="main-contents-body" class="main-contents-body">
            @yield('content')

            <div id="g-footer">
                <small>Copyright(C)At Home Co., Ltd.</small>
            </div>
        </div>
    </div>
</div>
<!-- /contents -->

</body>
</html>
