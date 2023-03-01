@extends('layouts.list-information')

@section('content')
<!-- 簡易設定 -->
<div class="main-contents info-list">
	<h1>アットホームからのお知らせ</h1>
	<div class="main-contents-body">
		<ul class="info-list-area">
			<?php foreach ($view->information as $key => $val) : ?>
				<li>
					<div class="info-l">
						<span><?php echo date('Y年m月d日', strtotime($val['start_date'])); ?></span>
					</div>

					<div class="info-r">
						<a href="/auth/detail/?id=<?php echo h($val['id']); ?>"><?php echo h($val['title']); ?></a>
						<?php if (is_array($val['file_list'])) : ?>
							<div class="info-file">
								<?php foreach ($val['file_list'] as $data) : ?>
									<a class="
									<?php if ($data['extension'] == 'doc' || $data['extension'] == 'docx') : ?> i-f-word <?php endif; ?>
									<?php if ($data['extension'] == 'xls' || $data['extension'] == 'xlsx') : ?> i-f-excel <?php endif; ?>
									<?php if ($data['extension'] == 'ppt' || $data['extension'] == 'pptx') : ?> i-f-pp <?php endif; ?>
									<?php if ($data['extension'] == 'pdf') : ?> i-f-pdf <?php endif; ?>" href="/auth/download/?file_id=<?php echo h($data['file_id']); ?>">
										<?php echo h($data['name'] . '.' . $data['extension']); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		{{$view->paginator->links('pagination-index',['search_param' => (isset($view->search_param) ? $view->search_param : false)])}}
		
		<div class="btns">
			<a href="/" class="btn-t-gray">戻る</a>
		</div>

	</div>
</div>
<!-- /簡易設定 -->
@stop

@section('script')
<script>
	//<!--
	$(document).ready(function() {
		$('#contents').addClass("w-fix");
	});
	//-->
</script>
@endsection