@extends('admin::layouts.default')

@section('title', __('CMSデータ削除'))

@section('script')
<script type="text/javascript" src="{{ asset('/js/admin/initialize_cms.js') }}"></script>
@endsection

@section('content')

		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>CMSデータ削除</h1>
			<div class="main-contents-body">

				<div class="section">

					<div class="alert-strong">
						下記会員さまのCMSデータを全て削除する機能です。十分注意した上でご利用ください。<br />
					</div>
					<table class="form-basic">
					<tr>
						<th>会員No</th>
						<td><?php echo h($view->company->member_no); ?></td>
					</tr>
					<tr>
						<th>会員名</th>
						<td><?php echo h($view->company->member_name); ?></td>
					</tr>
					<tr>
						<th>利用ドメイン</th>
						<td><?php echo h($view->company->domain); ?></td>
					</tr>
					</table>
				</div>

				<div style="text-align:center;">

				<?php if ($view->nocms == 0) { ?>
					<form acton="" method="post" name="form" id="form" target="initIframe">
						@csrf
						<input type="hidden" name="company_id" value="<?php echo h($view->params['company_id']); ?>">
						<input type="hidden" name="del_flg" value="1">
							<a href="/admin/company/detail?id=<?php echo h($view->params['company_id']); ?>" class="btn-t-gray">戻る</a>
							<button type="button" id="del" name="del" value="del"  class="btn-t-blue">CMSデータ削除</button>
					</form>
					<iframe name="initIframe" style="width:100%;border: solid 1px #a0a0a0;margin: 3px; display:none;"></iframe>
				<?php } else { ?>
					<div class="alert-strong">
                        この会員様はすでに初期化されています。<br />
                        詳細情報をお確かめくださいませ。
                    </div>
                    <a href="/admin/company/detail?id=<?php echo h($view->params['company_id']); ?>" class="btn-t-gray">戻る</a>
				<?php } ?>
				</div>
			</div>
		</div>
@endsection