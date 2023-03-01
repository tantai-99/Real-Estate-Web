<?php
namespace App\Http\Form;

use App;
use Library\Custom\Form;
use Library\Custom\Form\Element;

class PublishSpecial extends Form {

    private $specialRowset;

    public function init() {

        $actionName = getActionName();

        foreach ($this->specialRowset as $row) {

            $name = 'update';
            $elem = new Element\Checkbox("special_{$row->id}_{$name}");
            $elem->setAttribute('name', "special[{$row->id}][{$name}]");
            $elem->setAttribute('class', "{$name}_flg");
            $elem->setValue($actionName === 'simple');
            $this->add($elem);

            foreach (['new_release_flg', 'new_release_at', 'new_close_flg', 'new_close_at'] as $name) {

                $elem = new Element\Hidden("special_{$row->id}_{$name}");
                $elem->setAttribute('name', "special[{$row->id}][{$name}]");
                $elem->setAttribute('class', $name);
                $elem->setValue(0);
                $this->add($elem);
            }
        }
    }

    protected function setSpecialRowset($specialRowset) {

        $this->specialRowset = $specialRowset;
    }

    public function isValid($params, $checkErrors = true) {

        $validateFlg = parent::isValid($params);

        /* $paramsの構造メモ

        foreach ($params as $id => $param) {

            $param['update']          = (bool)$param['update'];
            $param['new_release_flg'] = (bool)$param['new_release_flg'];
            $param['new_release_at']  = strlen($param['new_release_at']) <= 1 ? null : $param['new_release_at'];
            $param['new_close_flg']   = (bool)$param['new_close_flg'];
            $param['new_close_at']    = strlen($param['new_close_at']) <= 1 ? null : $param['new_close_at'];

        }
        */

        return $validateFlg;
    }

}
