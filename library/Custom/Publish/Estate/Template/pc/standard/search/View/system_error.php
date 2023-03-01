<!DOCTYPE html>
<html lang="ja">
<head>
  <title>システムエラー</title>
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
</head>
<body id="top">
<div id="fb-root"></div>
<?php $file = 'header'; ?>
<?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.phpe.php"); ?>
<?php $file = 'gnav'; ?>
<?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.phpe.php"); ?>

<div class="contents contents-form">
  <div class="inner">
    <div class="contents-main-1column">
      <section>
        <h2 class="heading-lv1-1column">システムエラー</h2>
        <div class="element element-error">
          <h3 class="element-error-heading">サーバーに接続できません</h3>
          <p class="element-error-tx">ただいまサーバーが大変混み合っております。<br>
            ご迷惑をおかけし申し訳ございませんが、時間をおいて再度アクセスしていただきますようお願いいたします。
          </p>
        </div>
      </section>
    </div>
  </div>
</div>

<?php $file = 'footernav'; ?>
<?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.phpe.php"); ?>
<?php $file = 'footer'; ?>
<?php include(APPLICATION_PATH."/common/{$this->ua->requestDevice()}/_{$file}.blade.phpe.php"); ?>
</body>
</html>
