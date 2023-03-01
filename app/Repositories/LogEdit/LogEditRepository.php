<?php
namespace App\Repositories\LogEdit;

use App\Repositories\BaseRepository;
use App\Models\Information;
use Symfony\Component\Translation\t;
class LogEditRepository extends BaseRepository implements LogEditRepositoryInterface 
{
    
    protected $_name = 'log_edit';
    public function getModel()
    {
        return \App\Models\LogEdit::class;
   	}
    
    public function log($type, $staffId, $companyId, $editType, $hpId = null, $pageId = null, $attr1 = null, $userIp = null) 
    {
        $this->model->create(array(
                'type'				    => $type,
                'athome_staff_id'	    => $staffId,
                'page_id'			    => $pageId,
                'attr1'                 => $attr1,
                'hp_id'				    => $hpId,
                'company_id'		    => $companyId,
                'edit_type_code'	    => $editType,
                'datetime'			    => date('Y-m-d H:i:s'),
                'user_ip'	            => $userIp,
        ));
    }

    public function getDataLogForCompany($params)
    {
        $select = $this->model->select();
        $select->join("company as c", "log_edit.company_id", "=" ,"c.id");
        $select->select("log_edit.*","c.member_no", "c.member_name", "c.company_name", "c.contract_type");
        $select->where("c.delete_flg",0);
        $select->where("log_edit.delete_flg", 0);
        // ログ種別
        $select->where("log_edit.type", $params->logType);
        return $select;
    }
}