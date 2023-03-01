<?php
$agency = !getInstanceUser('cms')->isNerfedTop();
$serviceTop = getInstanceUser('cms')->checkHasTopOriginal();
?>
<div id="side">
	<div class="inner">
	<h1>基本設定</h1>
	<ul class="side-menu">
		<li class="<?php if(isCurrent('index')):?>is-active<?php endif;?>"><a class="i-m-set" href="<?php echo route('default.sitesetting.index')?>">初期設定</a></li>
        <?php if(!$serviceTop || $agency):?>
		<li class="<?php if(isCurrent('design')):?>is-active<?php endif;?>"><a class="i-m-design" href="<?php echo route('default.sitesetting.design') ?>">デザイン選択</a></li>
        <?php endif; ?>
		<li class="<?php if(isCurrent('image' )):?>is-active<?php endif;?>"><a class="i-m-img"    href="<?php echo route('default.sitesetting.image' )?>">画像フォルダ</a></li>
		<li class="<?php if(isCurrent('file2' )):?>is-active<?php endif;?>"><a class="i-m-file"   href="<?php echo route('default.sitesetting.file2' )?>">ファイル管理</a></li>
	</ul>
	</div>
</div>
