<?php
namespace Library\Custom\Model\Lists;

    class LogEditType extends ListAbstract {

    	static protected $_instance;

        const LOGIN                 = 1;

        const PAGE_CREATE           = 2;
        const PAGE_UPDATE           = 3;
        const PAGE_DELETE           = 4;

        const SITESETTING_UPDATE    = 5;
        const DESIGN_UPDATE         = 6;

        const IMAGE_CREATE          = 7;
        const PUBLISH_TEST          = 8;
        const PUBLISH               = 9;

        // 代行作成ログ -->>
        const CREATOR_DATA_COPY     = 10;
        const CREATOR_DATA_DELETE   = 11;
        const CREATOR_UPDATE        = 12;
        const CREATOR_TEST          = 13;
        const CREATOR_ROLLBACK      = 14;
        // 代行作成ログ <<--

        const CREATE_FILE2          = 15;


        //物件設定
        const ESTATE_SETTING_CREATE    = 20;
        const ESTATE_SETTING_UPDATE    = 21;
        const ESTATE_SETTING_DELETE    = 22;

        //特集設定
        const SPECIAL_SETTING_CREATE   = 31;
        const SPECIAL_SETTING_UPDATE   = 30;
        const SPECIAL_SETTING_DELETE   = 32;
        const SPECIAL_SETTING_COPY     = 33;

        //2次広告自動公開
        const SECOND_SETTING_CREATE    = 40;
        const SECOND_SETTING_UPDATE    = 41;
        //2次広告自動公開の除外設定
        const SECOND_SETTING_EXCLUSION_UPDATE    = 45;
        const SECOND_SETTING_EXCLUSION_DELETE    = 46;

        const SITE_DELETE    = 47;

        const IMAGE_DELETE   = 48;
        const DELETE_FILE2   = 49;
        
        protected $_list = array(
            self::LOGIN                 => 'ログイン',
            self::PAGE_CREATE           => 'ページ作成',
        	self::PAGE_UPDATE           => 'ページ編集',
        	self::PAGE_DELETE           => 'ページ削除',
        	self::SITESETTING_UPDATE    =>'初期設定登録',
        	self::DESIGN_UPDATE         => 'デザイン選択',
        	self::IMAGE_CREATE          => '画像登録',
        	self::IMAGE_DELETE          => '画像削除',
            self::PUBLISH_TEST          => '公開処理(テストサイト)',
        	self::PUBLISH               => '公開処理(本番サイト)',

        	self::CREATOR_DATA_COPY     => '代行作成データコピー',
        	self::CREATOR_DATA_DELETE   => '代行作成データ削除',
        	self::CREATOR_UPDATE        => '代行更新',
        	self::CREATOR_TEST          => '公開処理(代行テストサイト)',
        	self::CREATOR_ROLLBACK      => '代行作成ロールバック',

        	self::CREATE_FILE2			=> 'ファイル登録',
        	self::DELETE_FILE2			=> 'ファイル削除',

            self::ESTATE_SETTING_CREATE  => '物件設定作成',
            self::ESTATE_SETTING_UPDATE  => '物件設定更新',
            self::ESTATE_SETTING_DELETE  => '物件設定削除',

            self::SPECIAL_SETTING_CREATE  => '特集設定作成',
            self::SPECIAL_SETTING_UPDATE  => '特集設定更新',
            self::SPECIAL_SETTING_DELETE  => '特集設定削除',
            self::SPECIAL_SETTING_COPY    => '特集設定コピー',

            self::SECOND_SETTING_CREATE            => '２次広告自動公開設定作成',
            self::SECOND_SETTING_UPDATE            => '２次広告自動公開設定更新',
            self::SECOND_SETTING_EXCLUSION_UPDATE  => '２次広告自動公開除外設定更新',
            self::SECOND_SETTING_EXCLUSION_DELETE  => '２次広告自動公開除外設定削除',

            self::SITE_DELETE  => '非公開(CMS)',
        );

        static public function isPageLog($editType){
            $editTypes = [
                self::PAGE_CREATE,
                self::PAGE_UPDATE,
                self::PAGE_DELETE,
            ];
            return in_array($editType,$editTypes);
        }

        static public function isEstateLog($editType){
            $editTypes = [
                self::ESTATE_SETTING_CREATE,
                self::ESTATE_SETTING_UPDATE,
                self::ESTATE_SETTING_DELETE,
            ];
            return in_array($editType,$editTypes);

        }

        static public function isSpecialLog($editType){
            $editTypes = [
                self::SPECIAL_SETTING_CREATE,
                self::SPECIAL_SETTING_UPDATE,
                self::SPECIAL_SETTING_DELETE,
                self::SPECIAL_SETTING_COPY,
            ];
            return in_array($editType,$editTypes);

        }

        static public function isSecondEstateLog($editType){
            $editTypes = [
                self::SECOND_SETTING_CREATE,
                self::SECOND_SETTING_UPDATE,
                self::SECOND_SETTING_EXCLUSION_UPDATE,
                self::SECOND_SETTING_EXCLUSION_DELETE,
            ];
            return in_array($editType,$editTypes);
        }
    }
