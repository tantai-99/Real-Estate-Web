<?php
namespace App\Http\Form\Rating;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Models\Company;

use Date;
use DateTime;

class CompanySelect extends Form
{
    /**
     * @var App\Models\Hp
     */
    private $hp;

    public function hasMultiCompanies()
    {
        $element = $this->getElement('company_id');
        if (!$element) {
            return false;
        }

        return count($element->getValueOptions()) > 1;
    }

    public function setCompanyRow(Company $company)
    {
        $options = [];
        // 評価分析契約のアカウントは選択対象外にする
        if(!$company->isAnalyze()){
            $options[$company->id] = $company->member_name;
        }

        /** @var $company App\Models\Company */
        $now = new DateTime();
        foreach ($company->fetchAssociatedCompanies() as $childCompany) {
            if ($childCompany->getCurrentHp() !== false && !$childCompany->isAnalyze() && $childCompany->isAvailable()) {
                 $options[$childCompany->id] = $childCompany->member_name;
            }
        }

        $element = new Element\Select('company_id');
        $element->setAttributes([
            'required' =>true,
        ]);
        $element->setValueOptions($options);
        $this->add($element);
    }
    
    /**
     * @param string $name
     */
    public function getMultiOptions($name) {
        $element = $this->getElement($name);
        $options = $element->getValueOptions();
        return $options;
    }
    

}