<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>利用開始通知書│ホームページ作成ツール</title>

	<style>
		/* base ---------------------------------------------------- */
		body {
			color: #000;
			line-height: 1;
			min-width: 990px;
			width: 100%;
			height: 100%;
*			font-size: 8px;
			/* font-family:  "メイリオ","Hiragino Kaku Gothic ProN", sans-serif; */
			margin: 0;
			background: #FFF;
			padding: 30px;
			line-height: 1.4;
			box-sizing: border-box;
			-webkit-box-sizing: border-box;
		}

		p {
			margin: 0 0 10px;
  			line-height: 1.3;
		}

		a {
			color: #3785dd;
			text-decoration: none;
		}

		h1 {
			margin: 0;
			padding: 0;
			font-weight: normal;
			line-height: 1;
		}

		h3 {
			margin: 0 0 5px;
			line-height: 1.3;
			font-size: 12px;
		}

		h2, h4, h5 {
			margin: 0;
			padding: 0;
			font-weight: normal;
			line-height: -10;
		}


		/* main ---------------------------------------------------- */

		.section {
/*			margin-bottom: 42px;*/
			margin-bottom: 15px;
			position: relative;
		}

		h1 {
			font-size: 25px;
			padding: 0 20px;
			font-weight: bold;
			text-align: center;
			margin-bottom: 20px;
		}

		h2 {
/*			font-size: 22px;*/
			font-size: 16px;
			position: relative;
			margin: 5px 0;
		}

		h2:before {
			content: "";
			display: block;
			width: 4px;
			height: 18px;
			background-color: #4a596a;
			position: absolute;
			left: 0;
			top: 1px;
		}

		#g-footer {
			text-align: center;
			position: relative;
			margin-top: 30px;
			margin-bottom: 10px;
			color: #75879c;
		}

		#g-footer small {
			font-size: 12px;
		}


		/* table ---------------------------------------------------- */
		table {
			border-collapse: collapse;
			width: 100%;
			border: 1px solid #cad2d6;
		}

		thead td {
			background-color: #798ca1;
			color: #fff;
			padding: 6px 15px;
			font-weight: bold;
		}

		th {
			background: #F4F7FA;
			padding: 6px 15px;
			border: 1px solid #cad2d6;
			width: 28%;
			font-weight: 200;
			text-align: left;
		}

		td {
			padding: 6px 15px;
			border: 1px solid #cad2d6;
		}

		table + p {
			margin-top: 10px;
			margin-bottom: 20px;
		}


		/* element ---------------------------------------------------- */
		.notice-dear {
			margin-bottom: 10px;
			border-bottom: 2px solid #4a596a;
			display: table;
			width: 100%;
		}

		.notice-dear p {
			display: table-cell;
			font-size: 18px;
		}

		.notice-dear span{
			display: table-cell;
			font-size: 14px;
			padding-left: 40px;
			text-align: right;
			white-space: nowrap;
		}

		.notice-alert{
			font-size: 8px;
		}

		.notice-alert p {
			position: relative;
			padding-left: 13px;
		}

		.notice-alert span {
			position: absolute;
			top: 0;
			left: -10px;
		}

		.annotation {
			font-size: 10px;
		}

		.font-idPass {
			font-family: "Courier New"
		}

		.footer {
			width: 100%;
			margin-top: 0;
			text-align:right;
		}
	</style>
</head>

<body>
	<h1>【「ホームページ作成ツール」利用開始通知書】</h1>
		<div class="section">
			<div class="notice-dear">
				<p><?php echo h($view->company->member_name);?>　御中<span>（会員コード：<?php echo h($view->company->member_no);?>）</span></p>
			</div>
			<p>この度は、「ホームページ作成ツール」にお申し込みいただきまして誠にありがとうございます。<br />サービスのご利用準備が整いましたので、ご案内させていただきます。</p>
		</div>
		<div class="section">
			<h2>■現在のご利用の情報</h2>
			<table>
				<tbody>
					<tr>
						<th>ドメイン取得組織名</th>
						<td><?php echo h($view->company->company_name);?></td>
					</tr>
					<tr>
						<th>ご利用開始日</th>
						<td><?php echo h( $view->company->start_date_view ) ;?></td>
					</tr>
					<tr>
						<th>契約中プラン</th>
						<td>
							<?php
								$disp = '-'	;
								if ( $view->company->cms_plan )
								{
									$plan	=  $view->listCmsPlan->getCmsPLanNameByList( $view->company->cms_plan ) ;
									$disp	= "ホームページ作成ツール・{$plan}"	;
								}
								echo $disp ;
							?>
						</td>
					</tr>
					<tr>
						<th>取得されたドメイン情報</th>
						<td><?php echo h($view->company->domain);?></td>
					</tr>
				</tbody>
			</table>
			<p class="annotation">※なお、登録されている情報は <a href="https://www.onamae.com/domain/whois/" target="blank">https://www.onamae.com/domain/whois/</a> からご確認いただけます。</p>
		</div>

		<div class="section">
			<h2>■ご予約プラン</h2>
			<table>
				<tbody>
					<tr>
						<th>変更開始日</th>
						<td><?php echo h( $view->company->reserve_start_date_view ) ;?></td>
					</tr>
					<tr>
						<th>ご予約プラン</th>
						<td>
							<?php
								$disp = '-'	;
								if ( $view->company->reserve_cms_plan )
								{
									$plan	=  $view->listCmsPlan->getCmsPLanNameByList( $view->company->reserve_cms_plan ) ;
									$disp	= "ホームページ作成ツール・{$plan}"	;
								}
								echo $disp ;
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="section">
			<h2>■ホームページアドレス</h2>
			<table>
				<tbody>
					<tr>
						<th>ホームページアドレス</th>
						<td><?php if($view->company->domain != "") :?>https://www.<?php echo h($view->company->domain);?><?php endif;?></td>
					</tr>
				</tbody>
			</table>
			<p class="annotation">※こちらのホームページアドレスが貴社ホームページのアドレスになります。<br>
			※メールサービスは「サーバーコントロールパネル」で設定後利用ができます。</p>
		</div>

		<div class="section">
			<h2>■ホームページ作成関連ログイン情報</h2>
				<p>貴店ホームページを管理するツールは２種類あり、それぞれログインID・パスワード（初期設定）は異なりますのでご注意ください。<br>英数字サンプル：<span class="font-idPass">abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789</span></p>
				<h3>●CMS（ホームページを作成するツール）</h3>
				<table>
					<thead>
						<td colspan="2">ホームページを作成するウェブサイト（ホームページ作成ツール）</td>
					</thead>
					<tbody>
						<tr>
							<th>アドレス</th>
							<td>https://hpadvance.athome.jp</td>
						</tr>
						<tr>
							<th>ログインID</th>
							<td class="font-idPass"><?php echo h($view->account->login_id);?></td>
						</tr>
						<tr>
							<th>パスワード</th>
							<td class="font-idPass">
								<?php echo h( $view->account->password ) ; ?>
							</td>
						</tr>
					</tbody>
				</table>
				<br>

				<h3>●サーバーコントロールパネル（メールアドレス等を管理するツール）</h3>
				<table>
					<thead>
						<td colspan="2">メールアドレス管理・アクセスログ閲覧を行うウェブサイト（ホームページサーバーコントロールパネル）</td>
					</thead>
					<tbody>
						<tr>
							<th>アドレス</th>
							<td><?php echo h($view->company->cp_url);?></td>
						</tr>
						<tr>
							<th>ログインID</th>
							<td class="font-idPass"><?php echo h($view->company->cp_user_id);?></td>
						</tr>
						<tr>
							<th>パスワード</th>
							<?php if($view->company->cp_password_used_flg === 0) : ?>
								<td class="font-idPass">
								<?php echo h($view->company->cp_password);?>
							<?php else : ?>
								<td>
								現状のパスワードでログインできます。
							<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>
				<br>

				<h3>●オプション</h3>
				<table>
					<tbody>
						<tr>
							<th>2次広告自動公開</th>
							<td><?php echo h($view->secondEstate->isUse);?></td>
						</tr>
						<tr>
							<th>利用エリア</th>
							<td><?php echo h($view->secondEstate->area);?></td>
						</tr>
						<tr>
							<th>地図検索</th>
							<td><?php echo h( $view->mapOption ? '利用する' : '利用しない' );?></td>
						</tr>
                        <?php if (h($view->originalSetting->plan)) :?>
                        <tr>
                            <th>TOPオリジナル</th>
                            <td><?php echo h($view->originalSetting->isUse); ?></td>
                        </tr>
                        <?php endif; ?>
					</tbody>
				</table>
				<p class="annotation">※オプションサービスはホームページ作成ツール・アドバンスの場合、基本料金内でご利用いただけます（TOPオリジナルを除く）。<br>
			</div>
			<div class="section notice-alert">
				<h2>■ご注意</h2>
				<p><span>（１）</span>パスワードは当社にて設定した仮パスワードです。「CMS」、「サーバーコントロールパネル」の最初のご利用時にそれぞれのパスワードの変更をお願いします。</p>
				<p><span>（２）</span>貴店ホームページは、「CMS（ホームページを作成するツール）」にてページ作成後、公開処理を行うことでホームページが公開されます。<br />　　　公開処理を行うまでは貴店ホームページは公開されませんのでご了承ください。</p>
				<p><span>（３）</span>本サービスはサーバートラブルなどやむを得ない理由により、予告なくサービスを中止する場合があります。<br />　　　メンテナンスなどであらかじめサービスを中止することが分かっている場合は、「CMS」のログイン画面等、当社が指定する方法にて通知させていただきますので、ご確認をお願いいたします。</p>
			</div>
		</div>
		<div class="section">
			<div class="footer">
				<p>アットホーム株式会社<br><?php echo h($view->shozokuka);?></p>
			</div>
		</div>
<!-- /contents -->
</body>
</html>
