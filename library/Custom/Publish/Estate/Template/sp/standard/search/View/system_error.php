<!DOCTYPE html>
<html lang="ja">
<head>
  <?php $msg = 'システムエラー'; ?>
  <title><?= $msg; ?></title>
  <meta name="keywords" content="<?= $msg; ?>">
  <meta name="description" content="<?= $msg; ?>">
  <meta name="format-detection" content="telephone=no">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
</head>
<body class="top">
<div id="fb-root"></div>
<?php $file = 'header'; ?>
<?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.php"); ?>
<div class="contents">
  <?php $file = 'gnav'; ?>
  <?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.php"); ?>
  <h2 class="heading-lv1"><span>システムエラー</span></h2>
  <div class="element element-error">
    <h3 class="element-error-heading">サーバーに接続できません</h3>
    <p class="element-error-tx">ただいまサーバーが大変混み合っております。<br>
      ご迷惑をおかけし申し訳ございませんが、時間をおいて再度アクセスしていただきますようお願いいたします。
    </p>
  </div>
</div>
<?php $file = 'company_info'; ?>
<?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.php"); ?>
<p class="pagetop"><a href="#top"><span>ページの先頭へ</span></a></p>
<?php $file = 'footer'; ?>
<?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.php"); ?>
</body>
</html>
