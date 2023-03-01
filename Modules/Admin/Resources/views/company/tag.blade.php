@extends('admin::layouts.default')

@section('title', $view->original_tag)

@section('script')
<script type="text/javascript" src="/js/libs/jquery.ui.widget.js"></script>
<script type="text/javascript" src="/js/libs/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="/js/libs/jquery.fileupload.js"></script>
<script type="text/javascript" src="/js/admin/tag_regist.js"></script>
@endsection

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

		<form action="/admin/company/tag/company_id/{{$view->params['company_id']}}" method="post" name="form" enctype="multipart/form-data">
			@csrf
			<div class="section">

				<div class="m-tab">
					<a class="is-active">Googleアカウント情報</a>
					<a href="/admin/company/other-tag/company_id/{{$view->params['company_id']}}">その他タグ</a>
					<a href="/admin/company/other-estate-tag/company_id/{{$view->params['company_id']}}">その他タグ（物件問い合わせ）</a>
					<a href="/admin/company/other-estate-request-tag/company_id/{{$view->params['company_id']}}">その他タグ（物件リクエスト）</a>
					<!-- ATHOME_HP_DEV-4274: delete tag FDP
                        <a href="<?php //echo $this->url(array('controller' => 'company', 'action' => 'other-peripheral-tag', 'company_id' => $view->params['company_id']))
									?>">その他タグ（周辺環境問い合わせ）</a> -->
				</div>

				<?php $view->form->getSubForm('google')->form('id') ?>
				<?php $view->form->getSubForm('google')->form('company_id') ?>

				<input type="hidden" id="company_id" name="company_id" value="<?php echo h($view->params['company_id']); ?>" id="company_id">

				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('google')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden" && $element->getName() != "file_name") continue; ?>

						<tr<?php if ($element->isRequired()) : ?> class="is-require" <?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php if ($element->getName() == "file_name") : ?>
									<div class="f-img-upload">
										<div class="up-img">
											<div class="up-btn" upload-url="set-p12-file-upload">
												<?php // <input type="file" name="google[google_p12]" id="google-google_p12" onchange="uv.value = this.value;" /> 
												?>
												<input type="file" name="google[google_p12]" id="google-google_p12" />
											</div>
										</div>
										<div class="up-preview" style="width:100%;height:50%;">
											<input type="text" id="uv" disabled value="<?php echo h($view->form->getSubForm('google')->getElement('file_name')->getValue()); ?>">
											<?php $view->form->getSubForm('google')->form("file_name"); ?>
											<?php // <a href="" class="i-e-delete"></a> 
											?>
										</div>
									</div>
									<?php if ($view->form->getSubForm('google')->getElement("id")->getValue() > 0) : ?>
										<span style="font-size:10px;color:#848484">既に証明書ファイル（p12）は設定されています。変更の際は「ファイル選択」より設定してください。</span>
									<?php endif; ?>

								<?php else : ?>
									<?php $view->form->getSubForm('google')->form($name) ?>

								<?php endif; ?>

								<div class="errors" id="file_errors">
									<?php foreach ($element->getMessages() as $error) : ?>
										<?php echo h($error) ?>
									<?php endforeach; ?>
								</div>
							</td>
							</tr>
						<?php endforeach; ?>
				</table>
			</div>

			<?php /*
				<div class="section">
					<h2>その他タグ</h2>
					<table class="form-basic">
					<?php foreach ($this->form->other as $name => $element):?>
						<?php if($element->getType() == "hidden") continue; ?>
						<tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
							<th><span><?php echo $element->getLabel()?></span></th>
							<td>
								<?php $this->form->other->form($name)?>

								<?php foreach ($element->getMessages() as $error):?>
								<p style="color:red;"><?php echo h($error)?></p>
								<?php endforeach;?>
							</td>
						</tr>
						<?php endforeach;?>
					</table>
				</div>
				*/ ?>

			<div class="section" style="text-align:center;">
				<a href="/admin/company/detail?id=<?php echo /* $this->escape($view->params['company_id']) */ $view->params['company_id']; ?>" class="btn-t-gray">戻る</a>
				<input type="submit" id="submit" class="btn-t-blue" name="submit" value="確認">
			</div>
		</form>
	</div>
</div>
@endsection