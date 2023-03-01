<?php
namespace Modules\V1api\Services\Sp\Element;
use Modules\V1api\Services;
use Modules\V1api\Models\Settings;
use Library\Custom\Model\Estate;
class Tab {

    private $delete = ['rent', 'office', 'parking', 'others', 'mansion', 'house', 'land', 'business'];

    public function create($doc, Settings $settings, $stab=null, $ctab=null) {

        $checklistTab = $doc['div.element-search-tab'];
        $tab          = $doc['div.element-search-tab4'];

        if ($settings->search->isPurchaseShumokuOnly()) {

            $checklistTab['li.chintai']->remove();
        }
        elseif ($settings->search->isRentShumokuOnly()) {
            $checklistTab['li.baibai']->remove();
        }

        foreach ($settings->search->getShumoku() as $shumoku) {

            switch ((int)$shumoku) {
                case Estate\TypeList::TYPE_CHINTAI:
                    $this->unsetIfExistVal('rent');
                    break;
                case Estate\TypeList::TYPE_KASI_TENPO:
                case Estate\TypeList::TYPE_KASI_OFFICE:
                    $this->unsetIfExistVal('office');
                    break;
                case Estate\TypeList::TYPE_PARKING:
                    $this->unsetIfExistVal('parking');
                    break;
                case Estate\TypeList::TYPE_KASI_TOCHI:
                case Estate\TypeList::TYPE_KASI_OTHER:
                    $this->unsetIfExistVal('others');
                    break;
                case Estate\TypeList::TYPE_MANSION:
                    $this->unsetIfExistVal('mansion');
                    break;
                case Estate\TypeList::TYPE_KODATE:
                    $this->unsetIfExistVal('house');
                    break;
                case Estate\TypeList::TYPE_URI_TOCHI:
                    $this->unsetIfExistVal('land');
                    break;
                case Estate\TypeList::TYPE_URI_TENPO:
                case Estate\TypeList::TYPE_URI_OFFICE:
                case Estate\TypeList::TYPE_URI_OTHER:
                    $this->unsetIfExistVal('business');
                    break;
                default:
                    throw new \Exception('Illegal Argument.');
                    break;
            }
        }

        foreach ($this->delete as $class) {
            $tab['li.'.$class]->remove();
        }

        $checklistTab['li']->removeClass('active');
        if(!is_null($ctab) && count($checklistTab['li.' . $ctab]) > 0) {
            $checklistTab['li.' . $ctab]->addClass('active');
        } else {
        $checklistTab['li:first']->addClass('active');
        }

        $tab['li']->removeClass('active');
        if(!is_null($stab) && count($tab['li.' . $stab]) > 0) {
            $tab['li.' . $stab]->addClass('active');
        } else {
        $tab['li:first']->addClass('active');
        }

        return $doc;
    }

    private function unsetIfExistVal($string) {

        $i = array_search($string, $this->delete);
        if ($i !== false) {
            unset($this->delete[$i]);
        }
    }

    public function getDeleteTabList (Settings $settings) {
        // createと処理が重複するが、処理順の関係でメソッドは書き換えない
        $deletedTmp = array_flip($this->delete);

        foreach ($settings->search->getShumoku() as $shumoku) {
            $tmpKey = null;
            switch ((int)$shumoku) {
                case Estate\TypeList::TYPE_CHINTAI:
                    $tmpKey = 'rent';
                    break;
                case Estate\TypeList::TYPE_KASI_TENPO:
                case Estate\TypeList::TYPE_KASI_OFFICE:
                    $tmpKey = 'office';
                    break;
                case Estate\TypeList::TYPE_PARKING:
                    $tmpKey = 'parking';
                    break;
                case Estate\TypeList::TYPE_KASI_TOCHI:
                case Estate\TypeList::TYPE_KASI_OTHER:
                    $tmpKey = 'others';
                    break;
                case Estate\TypeList::TYPE_MANSION:
                    $tmpKey = 'mansion';
                    break;
                case Estate\TypeList::TYPE_KODATE:
                    $tmpKey = 'house';
                    break;
                case Estate\TypeList::TYPE_URI_TOCHI:
                    $tmpKey = 'land';
                    break;
                case Estate\TypeList::TYPE_URI_TENPO:
                case Estate\TypeList::TYPE_URI_OFFICE:
                case Estate\TypeList::TYPE_URI_OTHER:
                    $tmpKey = 'business';
                    break;
                default:
                    break;
            }
            if(!is_null($tmpKey) && isset($deletedTmp[ $tmpKey ])) {
               unset($deletedTmp[ $tmpKey ]);
            }
        }
        return array_keys($deletedTmp);
    } 
}