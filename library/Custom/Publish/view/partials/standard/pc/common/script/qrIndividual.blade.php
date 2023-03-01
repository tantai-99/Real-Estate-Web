<?php
  $this->viewHelper = new ViewHelper($this->_view);

  $typeTop  = 1;

  // ファイル名
  $db       = debug_backtrace();
  $filename = $this->viewHelper->getFileName(dirname($db[1]['file']));

  $thisPage = $this->viewHelper->getPageByFileName($filename) ? $this->viewHelper->getPageByFileName($filename) : $this->viewHelper->getPageByType($typeTop);
?>

<img src="/images/qr/<?php echo $thisPage['id'] ?>.png" alt="qr" style="width: 170px; height: 170px;">
