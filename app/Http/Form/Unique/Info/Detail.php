<?php

    class Custom_Hp_Page_Info_Detail extends Custom_Hp_Page_abstract {

        /**
         * フォーム生成（新規作成）
         *
         * @param $form
         *
         * @return mixed
         */
        public function getNewForm($form) {

            return parent::getNewForm($form);
        }

        /**
         * フォーム生成（編集）
         *
         * @param $form
         *
         * @return mixed
         */
        public function getEditForm($form) {

            return parent::getEditForm($form);
        }


        /**
         * 保存処理
         *
         */
        public function save() {

            parent::save();

        }

        /**
         * ページ一覧を取得
         */
        public function getList() {

        }
    }

?>