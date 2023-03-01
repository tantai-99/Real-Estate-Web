<?php

class footer {

    private $viewHelper;

    public function __construct($view) {

        $this->viewHelper = $view;
    }

    /**
     * 下階層のリンクを表示
     * - ディレクトリの場合は再帰的にさらに下を表示
     *
     * @param $footernav
     * @param $pages
     * @param $level
     */
    public function child($footernav, $pages) {

        if (!is_array($footernav)) {

            return;
        }

        echo '<ul>';
        foreach ($footernav as $page_id => $children) {

            $page = $pages[$page_id];

            echo '<li>';
            echo '<a '.$this->viewHelper->hpHref($page).'>'.htmlspecialchars($page['title']).'</a>';

            // 下層ページあり
            if (is_array($children)) {
                $this->child($children, $pages);

            }

            echo '</li>';
        }
        echo '</ul>';
    }
}

; ?>