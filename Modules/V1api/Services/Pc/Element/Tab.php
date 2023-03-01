<?php
namespace Modules\V1api\Services\Pc\Element;
use Modules\V1api\Models\Settings;
use Library\Custom\Model\Estate;

class Tab {

    private $delete = ['rent', 'office', 'parking', 'others', 'mansion', 'house', 'land', 'business'];

    public function delete($doc, Settings $settings, $stab=null, $ctab=null) {

        $checklistTab = $doc['div.checklist-tab'];
        $tab          = $doc['div.element-tab-search'];

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
            $ctab = $checklistTab['li:first']->attr('class');
            $checklistTab['li:first']->addClass('active');
        }

        $tab['li']->removeClass('active');

        if(!is_null($stab) && count($tab['li.' . $stab]) > 0) {
            $class = $stab;
        } else {
            $first = $doc['div.element-tab-search.' . $ctab . ' li:first'];
            $class = $first->attr('class');
        }

        $tab["li.{$class}"]->addClass('active');

        return $doc;
    }

    private function unsetIfExistVal($string) {

        $i = array_search($string, $this->delete);
        if ($i !== false) {
            unset($this->delete[$i]);
        }
    }

    public function getDeleteTabList () {

        return $this->delete;
    }
}
