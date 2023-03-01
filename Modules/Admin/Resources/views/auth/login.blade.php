@extends('admin::layouts.login')

@section('title', __('ホームページ作成ツール管理 - ログイン画面'))

@section('content')
<!--メインコンテンツ -->
<div class="main-contents login">
    <h1>ログイン</h1>
    <div class="main-contents-body">
        <?php if(app('request')->r==1 && getInstanceUser('admin')->isSessionTimeout()):?>
        <div class="alert-strong">セッションタイムアウト<br>60分間無操作状態が続いたため、自動的にログアウトしました。</div>
        <?php endif;?>
        <form action="{{route('admin.auth.login')}}" method="post">
            <div class="login-input">

				<?php foreach ($form->getElements() as $name=>$element):?>
					<?php echo $form->form($name);?>
                    <?php foreach ($element->getMessages() as $error):?>
						<p class="error">{{ __(h($error)) }}</p>
					<?php endforeach;?>
				<?php endforeach;?>
			</div>
            @csrf
            <div class="login-btn"><input type="submit" value="ログイン" class="btn-t-blue"></div>
        </form>
        <!-- <a href="" class="i-s-link">パスワードを忘れた方はこちら</a> -->
    </div>
</div>
@stop

<!-- @section('scriptjs')
    <script src="js/myjsfile.js"></script>
@stop -->
