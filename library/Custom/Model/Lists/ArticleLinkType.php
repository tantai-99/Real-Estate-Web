<?php
namespace Library\Custom\Model\Lists;

class ArticleLinkType extends ListAbstract {

    static protected $_instance;
    
    const LARGE_FOLDS = 1;
    const LARGE_EXPAND = 2;
    const SMALL_FOLDS = 3;
    const SMALL_EXPAND = 4;
    const ARTICLE = 5;
    
    protected $_list = array(
        self::LARGE_FOLDS => '大カテゴリーを表示<br><span>小カテゴリーを折りたたんで表示</span>',
        self::LARGE_EXPAND => '大カテゴリーを表示<br><span>小カテゴリーを展開して表示</span>' ,
        self::SMALL_FOLDS => '小カテゴリーを表示<br><span>記事を折りたたんで表示</span>' ,
        self::SMALL_EXPAND => '小カテゴリーを表示<br><span>記事を展開して表示</span>' ,
        self::ARTICLE => '記事のみ表示' ,
    );

    function getListTypeResult() {
        return array(
            self::LARGE_FOLDS => '大カテゴリーを表示／小カテゴリーを折りたたんで表示',
            self::LARGE_EXPAND => '大カテゴリーを表示／小カテゴリーを展開して表示' ,
            self::SMALL_FOLDS => '小カテゴリーを表示／記事を折りたたんで表示' ,
            self::SMALL_EXPAND => '小カテゴリーを表示／記事を展開して表示' ,
            self::ARTICLE => '記事のみ表示' ,
        );
    }

}