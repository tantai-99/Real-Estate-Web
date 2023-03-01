@extends('admin::layouts.default')

@section('title', $view->original_tag. 'タグ設定')

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1><?php echo $view->original_tag; ?></h1>
	<div style="width:100%;text-align:right;padding:5px;margin-top:-50px;">
		<a href="/admin/company/detail/?id=<?php echo h($view->params['company_id']); ?>" class="btn-t-gray">戻る</a>
	</div>
	<div class="main-contents-body">
		<div class="alert-normal">※登録する情報は本番サイトにのみ登録されます。テストサイトと代行作成サイトには登録されません。</div>
		<div class="section">
			<table class="form-basic">
				<tr>
					<th><span>会員No</span></th>
					<td><?php echo h($view->company->member_no); ?></td>
				</tr>
				<tr>
					<th><span>会社名</span></th>
					<td><?php echo h($view->company->company_name); ?></td>
				</tr>
				<tr>
					<th><span>利用ドメイン</span></th>
					<td><?php echo h($view->company->domain); ?></td>
				</tr>
			</table>
		</div>


		<form action="/admin/company/other-estate-request-tag/company_id/{{$view->params['company_id']}}" method="post" name="form" enctype="multipart/form-data">
			@csrf
			<div class="section">

				<div class="m-tab">
					<a href="/admin/company/tag/company_id/{{$view->params['company_id']}}">Googleアカウント情報</a>
					<a href="/admin/company/other-tag/company_id/{{$view->params['company_id']}}">その他タグ</a>
					<a href="/admin/company/other-estate-tag/company_id/{{$view->params['company_id']}}">その他タグ（物件問い合わせ）</a>
					<a class="is-active">その他タグ（物件リクエスト）</a>
					<!-- ATHOME_HP_DEV-4274: delete tag FDP
                        <a href="<?php //echo $view->url(array('controller' => 'company', 'action' => 'other-peripheral-tag', 'company_id' => $view->params['company_id']))
									?>">その他タグ（周辺環境問い合わせ）</a> -->


				</div>

				<?php $view->form->getSubForm('other')->form("id") ?>
				<?php $view->form->getSubForm('other')->form("company_id") ?>
				<input type="hidden" id="company_id" name="company_id" value="<?php echo h($view->params['company_id']); ?>" id="company_id">

				<?php $tag_title = array(
					'above_close_head_tag_residential_rental_request_thanks' => '居住用賃貸物件フォーム(サンクスページ)',
					'above_close_head_tag_business_rental_request_thanks'    => '事業用賃貸物件フォーム(サンクスページ)',
					'above_close_head_tag_residential_sale_request_thanks'   => '居住用売買物件フォーム(サンクスページ)',
					'above_close_head_tag_business_sale_request_thanks'      => '事業用売買物件フォーム(サンクスページ)',
					'above_close_head_tag_residential_rental_request_input'  => '居住用賃貸物件フォーム(入力フォームページ)',
					'above_close_head_tag_business_rental_request_input'     => '事業用賃貸物件フォーム(入力フォームページ)',
					'above_close_head_tag_residential_sale_request_input'    => '居住用売買物件フォーム(入力フォームページ)',
					'above_close_head_tag_business_sale_request_input'       => '事業用売買物件フォーム(入力フォームページ)',
				); ?>
				<?php foreach ($view->form->getSubForm('other')->getElements() as $name => $element) : ?>
					<?php if ($element->getType() == "hidden") continue; ?>

					<?php if (strpos($name, 'above_close_head_tag') === 0) : ?>
						<div class="section">
							<h2><?php echo $tag_title[$name]; ?></h2>
							<table class="form-basic">
							<?php endif; ?>

							<tr<?php if ($element->isRequired()) : ?> class="is-require" <?php endif; ?>>
								<th><span><?php echo $element->getLabel() ?></span></th>
								<td>
									<?php $view->form->getSubForm('other')->form($name) ?>

									<?php foreach ($element->getMessages() as $error) : ?>
										<p style="color:red;"><?php echo h($error) ?></p>
									<?php endforeach; ?>
								</td>
								</tr>

								<?php if (strpos($name, 'above_close_body_tag') === 0) : ?>
							</table>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>

			</div>

			<div class="section" style="text-align:center;">
				<a href="/admin/company/detail?id=<?php echo /* $view->escape($view->params['company_id']) */ $view->params['company_id']; ?>" class="btn-t-gray">戻る</a>
				<input type="submit" id="submit" class="btn-t-blue" name="submit" value="確認">
			</div>
		</form>
	</div>
</div>
@endsection