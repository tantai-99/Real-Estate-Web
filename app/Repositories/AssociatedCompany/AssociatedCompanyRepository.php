<?php

namespace App\Repositories\AssociatedCompany;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Traits\MySoftDeletes;

use function Symfony\Component\Translation\t;

class AssociatedCompanyRepository extends BaseRepository implements AssociatedCompanyRepositoryInterface
{
    // use MySoftDeletes;
    public function getModel()
    {
        return \App\Models\AssociatedCompany::class;
    }
    
    protected $_name = 'associated_company';

    /**
     * 会員Noで親に紐付く子供を取得
     * 
     * @param Int company_id
     * 
     */
    public function getDataForCompanyId($company_id)
    {
        $select = $this->model->select('member_no', 'company_name', 'company.id', 'associated_company.create_date');
        // and c.delete_flg = 0","c.member_no", "c.company_name", "c.id as company_id"
        $select->join("company", "associated_company.subsidiary_company_id", "=", "company.id");
        $select->where("associated_company.parent_company_id", $company_id);
        $select->where("company.delete_flg",0);
        $select->where('associated_company.delete_flg', 0);
        $select->orderBy("associated_company.id", "DESC");
        return $select->get();
    }

    /**
     * 既に子会社として紐図いているかチェックする
     * 
     * @param Int company_id  親契約者ID
     * @param Int subsidiary_company_id  子契約書ID
     * 
     */
    public function getDataForCompanyIdSsubsidiaryId($company_id, $subsidiary_company_id)
    {
        $select = $this->model->select();
        $select->where('parent_company_id', $company_id);
        $select->where('subsidiary_company_id', $subsidiary_company_id);
        $select->where('delete_flg', 0);
        return $select->first();
    }


    /**
     * 自身に紐付く子供の件数取得
     * 
     * @param Int company_id
     * 
     */
    public function getChildrenCountForCompanyId($company_id)
    {

        $select = $this->model->selectRaw("count(ac.id) as cnt")->withoutGlobalScopes();
        $select->from($this->_name . ' as ac');
        $select->where('ac.parent_company_id', $company_id);
        $select->where('ac.delete_flg', 0);
        return $this->fetchRow($select);
    }

    /**
     * 自身に紐付く親の件数取得
     * 
     * @param Int company_id
     * 
     */
    public function getParentCountForCompanyId($company_id)
    {

        $select = $this->model->selectRaw("count(ac.id) as cnt")->withoutGlobalScopes();
        $select->from($this->_name . ' as ac');
        $select->where('ac.subsidiary_company_id', $company_id);
        $select->where('ac.delete_flg', 0);
        return $this->fetchRow($select);
    }

    /**
     * 自身に紐付く子供取得
     * 
     * @param Int company_id
     * 
     */
    public function getChildrenForCompanyId($company_id)
    {

        $this->setAutoLogicalDelete(false);
        $select = $this->model->select();
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
    public function getParentForCompanyId($company_id)
    {

        $this->setAutoLogicalDelete(false);
        $select = $this->model->select();
        $select->from(array("ac" => $this->_name), array("id"));
        $select->Join(array("c" => "company"), "c.id = ac.parent_company_id AND c.delete_flg = 0", array("c.member_no", "c.company_name"));
        $select->where('ac.subsidiary_company_id = ?', $company_id);
        $select->where('ac.delete_flg = ?', 0);
        return $this->fetchAll($select);
    }

    public function getAssociatedCompany($cols) {
        $this->setAutoLogicalDelete(false);
        $select = $this->model->selectRaw(implode(',', $cols))->withoutGlobalScopes();
        $select->from('associated_company as ac');
        $select->leftJoin('company as c', 'c.id', '=', 'ac.parent_company_id');

        return $select;
    }
}
