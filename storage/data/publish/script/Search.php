<?php

$path_cms = APPLICATION_PATH.'/../data/publish/script/';
$path_gmo = APPLICATION_PATH.'/../script/';

$list = [
    'SearchPages.php',
    'SearchShumoku.php',
    'SearchTodofuken.php',
];

foreach ($list as $filename) {
    // gmo
    if (file_exists($path = $path_gmo.$filename)) {
        require_once($path);
        continue;
    }

    // cms
    if (file_exists($path = $path_cms.$filename)) {
        require_once($path);
    }
}

class Search {

    static public function reserved_word_all() {

        $res   = \SearchShumoku::dirname_all();
        $res[] = 'personal';
        $res[] = 'inquiry';
        $res[] = 'search';
        $res[] = 'api';
        $res[] = 'howtoinfo';

        return $res;
    }

}