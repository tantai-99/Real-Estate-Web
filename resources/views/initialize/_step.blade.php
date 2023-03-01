<?php
$step = array(
	'index'				=> '初期設定',
	'design'			=> 'デザイン選択',
	'top-page'			=> 'トップページ作成',
	'company-profile'	=> '会社紹介ページ作成',
	'privacy-policy'	=> 'プライバシーポリシー作成',
	'site-policy'		=> 'サイトポリシー作成',
	'contact'			=> 'お問い合わせ作成',
	'complete'			=> '完了',
);

$isNerfedTop = getInstanceUser('cms')->isNerfedTop();
if ($isNerfedTop) unset($step['design']);

$current = getActionName();
switch ($current) {
    case 'topPage':
        $current = 'top-page';
        break;
    case 'companyProfile':
        $current = 'company-profile';
        break;
    case 'privacyPolicy':
        $current = 'privacy-policy';
        break;
    case 'sitePolicy':
        $current = 'site-policy';
        break;
}
?>
<?php if (isCurrent('complete', 'initialize')) : ?>
	<h1>初回設定が完了しました。次の設定に進みましょう 。</h1>
<?php else : ?>
	<h1>はじめに、サイトの設定と基本ページを作りましょう。</h1>
<?php endif; ?>

<ul class="first-step">
	<?php $no = 1 ?>
	<?php $isFix = true ?>
	<?php $isCurrent = false ?>
	<?php foreach ($step as $action => $title) : ?>
		<?php if ($current == $action) : ?>
			<?php $isFix = false ?>
			<?php $isCurrent = true ?>
		<?php else : ?>
			<?php $isCurrent = false ?>
		<?php endif; ?>
		<li class="<?php if ($isFix) : ?>is-fix<?php elseif ($isCurrent) : ?>is-current<?php endif; ?>">
			<span><?php echo $no++ ?></span>
			<?php echo $title ?>
		</li>
	<?php endforeach; ?>
</ul>