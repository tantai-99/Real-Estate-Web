<?php

new h1($this->_view, $h1);

/**
 * h1の表示
 * Class h1
 */
class h1 {

    public $viewHelper;

    public function __construct($viewObj, $h1 = null) {

        if ($h1 !== null) {
            echo $h1;
            return;
        }

        $this->viewHelper = new ViewHelper($viewObj);

        // ページ一覧
        $hp = unserialize($this->viewHelper->getContentSettingFile('hp.txt'));

        // ファイル名
        $db       = debug_backtrace();
        $filename = $this->viewHelper->getFileName(dirname($db[2]['file']));

        $thisPage = $this->viewHelper->getPageByFileName($filename);
        if ($thisPage) {
            $h1 = "<<{$thisPage['title']}>>{$hp['outline']}";
        }
        // 以下、ファイル名をcmsで登録しないページ
        // サイトマップ
        elseif ($filename === 'sitemap') {
            $title = 'サイトマップ';
            $h1    = "<<{$title}>>{$hp['outline']}";
        }
        // 404
        elseif ($filename === '404notFound') {
            $title = 'ページが見つかりません';
            $h1    = "<<{$title}>>{$hp['outline']}";
        }
        // トップページ
        else {
            $h1 = $hp['outline'];
        }
        echo '<h1 class="tx-explain">'.htmlspecialchars($h1).'</h1>';
    }
}

?>
