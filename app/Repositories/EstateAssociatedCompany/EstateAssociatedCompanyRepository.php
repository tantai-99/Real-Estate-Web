<?php

namespace App\Repositories\EstateAssociatedCompany;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;

use function Symfony\Component\Translation\t;

class EstateAssociatedCompanyRepository extends BaseRepository implements EstateAssociatedCompanyRepositoryInterface
{	
	protected $_name = 'estate_associated_company';
    public function getModel()
    {
        return \App\Models\EstateAssociatedCompany::class;
    }
    public function getDataByCompanyId($company_id) {
        return  $this->model->where('parent_company_id', $company_id)
                            ->where('delete_flg', 0)
                            ->orderBy('id', 'DESC')->get();
    }
    /**
     * 既に子会社として紐図いているかチェックする
     * 
     * @param Int company_id  親契約者ID
     * @param Int subsidiary_company_id  子契約書ID
     * 
     */
    public function getDataByCompanyIdSubsidiaryId($company_id, $subsidiary_member_no) {
        return $this->model->where('parent_company_id', $company_id)
                            ->where('subsidiary_member_no', $subsidiary_member_no)
                            ->where('delete_flg', 0)->first();
    }

    /**
     * 自身に紐付く子供の件数取得
     * 
     * @param Int company_id
     * 
     */
    public function getChildrenCountByCompanyId($company_id) {

        $select = $this->model->selectRaw("count(ac.id) as cnt")->withoutGlobalScopes();
        $select->from($this->_name . ' as ac');
        $select->where('ac.parent_company_id', $company_id);
        $select->where('ac.delete_flg', 0);
        return $this->fetchRow($select);
    }
    /**
     * 自身に紐付く親の件数取得
     * 
     * @param Int member_no
     * 
     */
    public function getParentCountByCompanyId($member_no) {

        $select = $this->model->selectRaw("count(ac.id) as cnt")->withoutGlobalScopes();
        $select->from($this->_name . ' as ac');
        $select->where('ac.subsidiary_member_no', $member_no);
        $select->where('ac.delete_flg', 0);
        return $this->fetchRow($select);
    }

    /**
     * 自身に紐付く子供取得
     * 
     * @param Int company_id
     * 
     */
    public function getChildrenByCompanyId($company_id) {

        $this->setAutoLogicalDelete(false);
        $select = $this->select()->setIntegrityCheck(false);
        $select->from(array("ac" => $this->_name), array("id"));
        $select->Join(array("c" => "company"), "c.id = ac.subsidiary_company_id AND c.delete_flg = 0", array("c.member_no", "c.company_name"));
        $select->where('ac.parent_company_id = ?', $company_id);
        $select->where('ac.delete_flg = ?', 0);
        return $this->fetchAll($select);
    }

    /**
     * 自身に紐付く親取得
     * 
     * @param Int company_id
     * 
     */
    public function getParentByCompanyId($company_id) {

        $this->setAutoLogicalDelete(false);
        $select = $this->select()->setIntegrityCheck(false);
        $select->from(array("ac" => $this->_name), array("id"));
        $select->Join(array("c" => "company"), "c.id = ac.parent_company_id AND c.delete_flg = 0", array("c.member_no", "c.company_name"));
        $select->where('ac.subsidiary_company_id = ?', $company_id);
        $select->where('ac.delete_flg = ?', 0);
        return $this->fetchAll($select);
    }
    
    public function getEstateAssociatedCompany($cols) {
        $this->setAutoLogicalDelete(false);
        $select = $this->model->selectRaw(implode(',', $cols))->withoutGlobalScopes();
        $select->from('estate_associated_company as es');
        $select->leftJoin('company as c', 'c.id', '=', 'es.parent_company_id');

        return $select;
    }
}