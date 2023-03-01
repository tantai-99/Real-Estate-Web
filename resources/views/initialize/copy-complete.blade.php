@extends('layouts.login-deputize')

@section('content')
<?php
    $agency = !getInstanceUser('cms')->isAgency();
?>
<!-- メインコンテンツ -->
<div class="main-contents account">
    <h1><?php if ($agency): ?>代行作成<?php else: ?>制作代行<?php endif; ?>コピー</h1>
    <div class="main-contents-body">
            @csrf
            <div class="section">
                <h2>加盟店データのコピーが完了しました。</h2>

                @include('_deputize-info')

            </div>
            <div class="btns-center">
                <a class="btn-t-gray size-l" href="<?php echo $view->route('index', 'index')?>">次へ</a>
            </div>
    </div>
</div>
<!-- /メインコンテンツ -->
@endsection
