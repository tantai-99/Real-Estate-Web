@extends('layouts.login-deputize')

@section('content')
<!-- メインコンテンツ -->
<div class="main-contents account">
	<h1>会員選択</h1>
	<div class="main-contents-body">
		<form action="{{route('creator.select-company')}}" method="post">
			@csrf
			<input type="hidden" name="TantoCD" value="">
			<div class="section">
				<h2>制作代行する会員を選びましょう</h2>
				<div class="deputize-set">
					<dl>
					<?php foreach ($view->form->getElements() as $name => $element):?>
						<dt><?php echo $view->form->getElement($name)->getLabel()?></dt>
						<dd>
							<?php $view->form->form($name)?>
							<?php foreach ($element->getMessages() as $error):?>
								<p class="error"><?php echo h($error)?></p>
							<?php endforeach;?>
						</dd>
					<?php endforeach;?>
					</dl>
				</div>
			</div>
			<div class="btns-center">
				<input type="submit" value="次へ" class="btn-t-blue size-l">
			</div>
		</form>
	</div>
</div>
<!-- /メインコンテンツ -->
@endsection
