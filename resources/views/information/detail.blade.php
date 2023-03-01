@extends('layouts.default')

@section('content')
<!-- 簡易設定 -->
<div class="main-contents info-detail">
	<h1>アットホームからのお知らせ</h1>
	<div class="main-contents-body">
		<h2><?php echo h($view->information->title); ?></h2>

		<div class="info-text">
			<span><?php echo date('Y年m月d日', strtotime($view->information->start_date)); ?></span>

			<?php if ($view->information->display_type_code == config('constants.information_display_type_code.URL')) : ?>
				<a href="<?php echo $view->information->url; ?>" target="_blank">
					<p><?php echo $view->information->url; ?></p>
				</a>
		</div>

	<?php elseif ($view->information->display_type_code == config('constants.information_display_type_code.DETAIL_PAGE')) : ?>
		<p><?php echo nl2br($view->information->contents); ?></p>
	</div>

	<div class="info-file">
		<?php if (isset($view->params['name']) != "") : ?>
			<?php foreach ($view->params['name'] as $key => $val) : ?>
				<a class="
							<?php if ($view->params['extension_check'][$key] == 'doc' || $view->params['extension_check'][$key] == 'docx') : ?> i-f-word <?php endif; ?>
							<?php if ($view->params['extension_check'][$key] == 'xls' || $view->params['extension_check'][$key] == 'xlsx') : ?> i-f-excel <?php endif; ?>
							<?php if ($view->params['extension_check'][$key] == 'ppt' || $view->params['extension_check'][$key] == 'pptx') : ?> i-f-pp <?php endif; ?>
							<?php if ($view->params['extension_check'][$key] == 'pdf') : ?> i-f-pdf <?php endif; ?>" href="/default/information/download/?file_id=<?php echo h($view->params['file_id'][$key]) ?>">
					<?php echo h($view->params['file_name'][$key]); ?></a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

<?php elseif ($view->information->display_type_code == config('constants.information_display_type_code.FILE_LINK')) : ?>
</div>
<div class="info-file">
	<a class="
							<?php if ($view->params['extension_check'] == 'doc' || $view->params['extension_check'] == 'docx') : ?> i-f-word <?php endif; ?>
							<?php if ($view->params['extension_check'] == 'xls' || $view->params['extension_check'] == 'xlsx') : ?> i-f-excel <?php endif; ?>
							<?php if ($view->params['extension_check'] == 'ppt' || $view->params['extension_check'] == 'pptx') : ?> i-f-pp <?php endif; ?>
							<?php if ($view->params['extension_check'] == 'pdf') : ?> i-f-pdf <?php endif; ?>" href="/default/information/download/?id=<?php echo $view->information->id; ?>">
		<p><?php echo $view->params['file_name'] ?></p>
	</a>
</div>
<?php endif; ?>

<div class="btns">
	<!--					<a href="/default/index" class="btn-t-gray">戻る</a>-->
	<a href="javascript:;" onclick="history.back();" class="btn-t-gray">戻る</a>
</div>
</div>
</div>
<!-- /簡易設定 -->
@endsection

@section('script')
<script>
	//<!--
	$(document).ready(function() {
		$('#contents').addClass("w-fix");
	});
	//-->
</script>
@endsection