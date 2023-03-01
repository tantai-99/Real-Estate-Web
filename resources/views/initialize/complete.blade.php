@extends('layouts.initialize')
@section('content')
<div class="main-contents">

	<div class="main-contents-body">

		@include('initialize._step')

		<div class="section flow">
			<h2>基本的な設定の流れ</h2>
			<p>同様の内容がマニュアル「ホームページ作成から公開までの流れ」で確認できます。</p>

			<div class="contents-flow">
				<dl>
					<dt><img src="/images/first/fix_img1.png" alt="ページ作成/更新"></dt>
					<dd>
						<h3>ページを増やしていきましょう！</h3>
						<ul>
							<li>
								<span class="flow-step">1</span>
								<div class="flow-text">
									<h4>メニュー設定画面</h4>
									<div>
										メニュー設定画面には「スタッフ紹介」や「街情報」など不動産に関連するページがあらかじめセットされています。<br>
										※サービスとして対応していないページは削除が可能です。
										<div class="flow-point">
											<img src="/images/first/point.png" alt="POINT">
											<p>貴社独自のサービスやサイト訪問者に有益な情報を入力し、アピールをしましょう。</p>
										</div>
									</div>
								</div>
								<div class="flow-img">
									<img src="/images/first/fix_img1-1.png" alt="">
								</div>
							</li>

							<li>
								<span class="flow-step step2">2</span>
								<div class="flow-text">
									<h4>ページの作成画面</h4>
									<div>
										各ページに関連する項目があらかじめセットされています。
										項目に沿って情報を入力してみましょう。
									</div>
								</div>
								<div class="flow-img">
									<img src="/images/first/fix_img1-2.png" alt="">
								</div>
							</li>

							<li>
								<span class="flow-step">3</span>
								<div class="flow-text">
									<h4>ページの保存</h4>
									<div>
										画面右側に保存ボタンがあります。作成した内容を保存しましょう。<br>
										※この「保存」で公開はされません。
									</div>
								</div>
								<div class="flow-img">
									<img src="/images/first/fix_img1-3.png" alt="">
								</div>
							</li>
						</ul>
					</dd>
				</dl>

				<dl class="flow-2">
					<dt><img src="/images/first/fix_img2.png" alt="公開設定"></dt>
					<dd>
						<h3>忘れず公開処理をしましょう！</h3>
						<p>
							ページの追加・修正をした際は、必ず公開の処理を行ってください。<br>
							<strong>※公開設定画面にて公開の処理を行わないと公開がされません。</strong>
						</p>
					</dd>
				</dl>

				<dl class="last">
					<dt><img src="/images/first/fix_img3.png" alt="ページが公開されます "></dt>
					<dd>
						<div>
							<h4>公開されているサイトの状況を確認しましょう。</h4>
							<p>アクセス数やページの作成状況を「評価・分析」から閲覧できます。</p>
							<img src="/images/first/fix_img3-1.png" alt="">
						</div>
					</dd>
				</dl>

			</div>
		</div>

		<div class="btns">
			<a href="/site-map" class="btn-t-blue size-l">メニュー設定へ</a>
		</div>
	</div>
</div>
@endsection