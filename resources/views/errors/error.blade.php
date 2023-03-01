@extends('layouts.error')

@section('content')
<div class="main-contents">
	<div class="error-message">
		<p><?php echo $error ?></p>
		<div class="btn-area">
			<a href="/" class="btn-t-gray size-l">ホームへ戻る</a>
		</div>
	</div>
	
	<?php if (isset($exception) && config('environment.display_exceptions')): ?>

	<h3>Exception information:</h3>
	<p>
	    <b>Message:</b> <?php echo $exception->getMessage() ?>
	</p>
	
	<h3 class="mt20">Stack trace:</h3>
	<pre><?php echo $exception->getTraceAsString() ?></pre>
	
	<h3 class="mt20">Request Parameters:</h3>
	<pre><?php echo var_export(app('request')->all(), true) ?></pre>
	
	<?php endif ?>
</div>
@stop

