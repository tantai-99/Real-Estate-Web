<div class="deputize-set">
	<?php $profile = getInstanceUser('cms')->getProfile()?>
	<dl>
		<dt><span>会員No</span></dt>
		<dd><?php echo h($profile->member_no)?></dd>
	</dl>
	<dl>
		<dt><span>会員名</span></dt>
		<dd><?php echo h($profile->getDisplayCompanyName())?></dd>
	</dl>
</div>
