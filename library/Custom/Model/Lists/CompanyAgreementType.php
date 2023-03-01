<?php
namespace Library\Custom\Model\Lists;

class CompanyAgreementType Extends ListAbstract
{
    protected $_list;

    public function __construct()
    {
        $this->_list=array(
            config('constants.company_agreement_type.CONTRACT_TYPE_PRIME')   => '本契約',
            config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE') => '評価・分析のみ契約',
            config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')    => 'デモ',
        );
    }
}

