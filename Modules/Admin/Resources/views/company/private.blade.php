@extends('admin::layouts.default')

@section('title', __('非公開設定'))

@section('content')

<script type="text/javascript" src="{{ asset('/js/admin/company_html_delete.js')}}"></script>

		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>非公開設定</h1>
			<div class="main-contents-body">

				<div class="section">

					<div class="alert-strong">
						下記会員さまのホームページを全て削除する機能です。十分注意した上でご利用ください。<br />
						※CMS上のデータは残っています。
					</div>
					<table class="form-basic">
					<tr>
						<th>会員No</th>
						<td><?php echo h($company['member_no']); ?></td>
					</tr>
					<tr>
						<th>会員名</th>
						<td><?php echo h($company['member_name']); ?></td>
					</tr>
					<tr>
						<th>利用ドメイン</th>
						<td><?php echo h($company['domain']); ?></td>
					</tr>
					</table>
				</div>

				<div style="text-align:center;">
				<?php if($ftp_flg == true) : ?>
					<form acton="" method="post" name="form" id="form" >
						@csrf
						<input type="hidden" name="company_id" value="<?php echo h($id); ?>">
						<input type="hidden" name="del_flg" value="1">
							<a href="/admin/company/detail?id=<?php echo h($id); ?>" class="btn-t-gray">戻る</a>
							<button type="button" id="del" name="del" value="del"  class="btn-t-blue">削除</button>
					</form>
				<?php else : ?>
					<div class="alert-strong">
						この会員様はFTP情報または、ホームページ情報がありません。<br />
						詳細情報をお確かめくださいませ。
					</div>
					<a href="/admin/company/detail?id=<?php echo h($id); ?>" class="btn-t-gray">戻る</a>	
				<?php endif;?>
				</div>
			</div>
		</div>
@endsection