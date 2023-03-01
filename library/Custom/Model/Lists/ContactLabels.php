<?php
namespace Library\Custom\Model\Lists;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepository;

class ContactLabels extends ListAbstract {

    static protected $_instance;

    protected $_list = array();

    public function __construct() {

        $this->_list = \App::make(HpContactPartsRepositoryInterface::class)->getLabels();
    }

    protected $_chinese = array(
        HpContactPartsRepository::SUBJECT                   => '咨询内容',
        HpContactPartsRepository::PERSON_NAME               => '姓名',
        HpContactPartsRepository::PERSON_MAIL               => '电子邮件',
        HpContactPartsRepository::PERSON_TEL                => '电话',
        HpContactPartsRepository::PERSON_OTHER_CONNECTION   => '其他联系方式',
        HpContactPartsRepository::PERSON_TIME_OF_CONNECTION => '希望联系时间段',
        HpContactPartsRepository::PERSON_ADDRESS            => '地址',
        HpContactPartsRepository::PERSON_GENDER             => '姓别',
        HpContactPartsRepository::PERSON_AGE                => '年龄',
        HpContactPartsRepository::PERSON_JOB                => '职业',
        HpContactPartsRepository::PERSON_OFFICE_NAME        => '办公室名称',
        HpContactPartsRepository::PERSON_NUMBER_OF_FAMILY   => '住户人数',
        HpContactPartsRepository::PERSON_ANNUAL_INCOM       => '年收入',
        HpContactPartsRepository::PERSON_OWN_FUND           => '自有资金',
        HpContactPartsRepository::PERSON_CURRENT_HOME_CLASS => '目前居住分类',
        HpContactPartsRepository::PERSON_CURRENT_HOME_FORM  => '目前居住形式',
        HpContactPartsRepository::PROPERTY_TYPE             => '物件の種別',
        HpContactPartsRepository::PROPERTY_ADDRESS          => '物件の住所',
        HpContactPartsRepository::PROPERTY_EXCLUSIVE_AREA   => '専有面積',
        HpContactPartsRepository::PROPERTY_BUILDING_AREA    => '建物面積',
        HpContactPartsRepository::PROPERTY_LAND_AREA        => '土地面積',
        HpContactPartsRepository::PROPERTY_NUMBER_OF_HOUSE  => '総戸数',
        HpContactPartsRepository::PROPERTY_LAYOUT           => '間取り',
        HpContactPartsRepository::PROPERTY_AGE              => '築年数',
        HpContactPartsRepository::PROPERTY_STATE            => '物件の現況',
        HpContactPartsRepository::PROPERTY_CELL_REASON      => '売却理由',
        HpContactPartsRepository::PROPERTY_HOPE_LAYOUT      => '希望的房间布局',
        HpContactPartsRepository::PROPERTY_MOVEIN_PLAN      => '入住预定时间',
        HpContactPartsRepository::PROPERTY_BUDGET           => '预算（万日元）',
        HpContactPartsRepository::COMPANY_NAME              => '公司名称',
        HpContactPartsRepository::COMPANY_BUSINESS          => '业务内容',
        HpContactPartsRepository::COMPANY_PERSON            => '担当姓名',
        HpContactPartsRepository::COMPANY_PERSON_POST       => '担当者职位',
        HpContactPartsRepository::NOTE                      => '备注',
        HpContactPartsRepository::FREE_1                    => '自有项目１',
        HpContactPartsRepository::FREE_2                    => '自有项目２',
        HpContactPartsRepository::FREE_3                    => '自有项目３',
        HpContactPartsRepository::FREE_4                    => '自有项目４',
        HpContactPartsRepository::FREE_5                    => '自有项目５',
    );
}


