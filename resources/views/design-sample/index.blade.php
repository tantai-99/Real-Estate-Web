
@extends('layouts.design-sample')
@section('content')
<?php if(ctype_xdigit(request()->color_code)) : ?>
<img id="color_code_img" src="/cms_designselectpage/theme/<?php echo h(request()->theme)?>/color/<?php echo h(request()->type)?><?php if(request()->type == 'pc'):?>_<?php echo h(request()->layout)?><?php endif;?>.png?<?php echo time()?>">
	<script>
	$(function(){
		$("#color_code_img").bind("load", function() {
			$("#color_code_img").css({"background-color" : "#<?php echo htmlentities(request()->color_code); ?>"});
		});
	});
	</script>
<?php else : ?>
<img src="/cms_designselectpage/theme/<?php echo h(request()->theme)?>/color/<?php echo h(request()->color)?>/<?php echo h(request()->type)?><?php if(request()->type == 'pc'):?>_<?php echo h(request()->layout)?><?php endif;?>.jpg?<?php echo time()?>">
<?php endif; ?>

@endsection