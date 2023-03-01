<?php
namespace App\Repositories\Company;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App;
use Illuminate\Http\Request;
use App\Models\CompanyAccount;
use Exception;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
// use App\Traits\MySoftDeletes;

class CompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{
    // use MySoftDeletes;
    public function getModel()
    {
        return \App\Models\Company::class;
    }

    public function searchData(Request $request,$search_form,$params)
    {    
        
        $select = $this->model->select();
        if($request->has('search_id') && $request->search_id != "" && is_numeric($request->search_id)) {
            $select = $select->where('id', $request->search_id);
        }
        else 
        {
            if($search_form->isValid($params))
            {
                
                //契約
                if($request->has("contract_type") && $request->contract_type != "") {
                    $select=$select->where("contract_type", $request->contract_type);
                   
                }
                //会員
                if($request->has("member_no") && $request->member_no != "") {
                    $select=$select->where("member_no","like",'%'.$request->member_no.'%');
                }
                //会社名
                if($request->has("company_name") && $request->company_name != "") {
                    $select=$select->where("company_name","like", '%'.$request->company_name.'%');
                }
                //利用開始日
                if(($request->has("start_date_s") && $request->start_date_s != "") && ($request->has("start_date_e") && $request->start_date_e != "")) {
                    $select=$select->where("start_date" ,">=", $request->input('start_date_s') .' 00:00:00');
                    $select=$select->where('start_date','<=', $request->input('start_date_e') .' 23:59:59');
                    

                }else if(($request->has("start_date_s") && $request->start_date_s != "") && ($request->has("start_date_e") && $request->start_date_e == "")) {
                    $select=$select->where("start_date", "!=", '0000-00-00 00:00:00');       
                    $select=$select->where("start_date",">=", $request->input('start_date_s')." 00:00:00");

                }else if(($request->has("start_date_s") && $request->start_date_s == "") && ($request->has("start_date_e") && $request->start_date_e != "")) {
                    $select=$select->where("start_date","!=",'0000-00-00 00:00:00')->where("start_date",">=",$request->input('start_date_e')." 23:59:59");
                }
                //利用停止日
                if(($request->has("end_date_s") && $request->end_date_s != "") && ($request->has("end_date_e") && $request->end_date_e != "")) {
                    $select=$select->where("end_date",">=", $request->end_date_s ." 00:00:00");
                    $select=$select->where("end_date" ,"<=", $request->end_date_e ." 23:59:59");

                }else if(($request->has("end_date_s") && $request->end_date_s != "") && ($request->has("end_date_e") && $request->end_date_e == "")) {
                    $select=$select->where("end_date","!=",'0000-00-00 00:00:00')
                                ->where("end_date",">=", $request->end_date_s." 00:00:00");

                }else if(($request->has("end_date_s") && $request->end_date_s == "") && ($request->has("end_date_e") && $request->end_date_e != "")) {
                    $select=$select->where("end_date","!=",'0000-00-00 00:00:00')->where("end_date", "<=", $request->end_date_e ." 23:59:59");
                }
            }
        }
        return $select->orderby('id','desc')->paginate(20);
    }

    public function fetchLoginProfile($login_id, $password)
    {
        $select = CompanyAccount::where('login_id', $login_id)->where('password', $password)->first();
        if (!$select) {
            return false;
        }
        $row = $select->company()->first();
        if (!$row) {
            return false;
        }

        if (!$row->isAvailable()) {
            return false;
        }

        return $row;
    }
    
    public function setAutoLogicalDelete($isAuto = true) {
        $this->_auto_logical_delete = $isAuto;
        return $this;
    }

    public function fetchLoginProfileByMemberNo($member_no)
    {

        // $select = CompanyAccount::where('login_id', $login_id)->where('password', $password)->first();
        $cols = array(
            'company.id',
            'company.contract_type',
            'company.member_no',
            'company.member_name',
            'company.company_name',
            'company.domain',
            'company.start_date',
            'company.end_date',
            'company.map_start_date',
            'company.map_end_date',
            'company.cms_plan',
            'company.reserve_start_date',
            'company_account.login_id'
        );

        $this->setAutoLogicalDelete(false);

        $select = $this->model->withoutGlobalScopes()->select($cols);
        $select->from('company');
        $select->join('company_account', 'company.id', '=', 'company_account.company_id');
        $select->where('company.member_no', $member_no);
        $select->where('company.delete_flg', 0);
        $select->where('company_account.delete_flg', 0);

        $row = $select->first();

        $this->setAutoLogicalDelete(true);
        // if (is_null($select)) {
        //     return $select;
        // }
        // $row = $select->company()->first();

        if (!$row) {
            return false;
        }
        
        if ($row->isAnalyze()) {
        	return false;
        }

        if (!$row->isAvailable()) {
            return false;
        }

        return $row;
    }

    /**
     * 加盟店IDで取得
     */
    public function getDataForId($id)
    {
		if(!preg_match("/^[0-9 ]{1,}$/", $id)) {
			throw new Exception("加盟店IDに数字以外が指定されています");
		}
        $select=$this->model->select();
        $select->where('id', $id);
        return $select->first();
    }

    /**
     * 加盟店Noの存在チェック
     */
    public function getDataForMemberNo($member_no, $id = 0)
    {
        $select = $this->model->select();
        $select->where('member_no', $member_no);
        if ($id > 0)
        {
            $select->where('id','!=', $id);
        }
        return $select->where('member_no', $member_no)->get();

    }

    /**
     * ドメインの存在チェック
     */
    public function getDataForDomain($domain, $id = 0)
    {
        $select = $this->model->select();
        $select->where('domain', $domain);
        if ($id > 0) $select->where('id', '!=', $id);
        return $select->get();
    }

    public function fetchRowByHpId($hpId)
    {

        $table = App::make(AssociatedCompanyHpRepositoryInterface::class);

        // current hp id をチェック
        $assoc = $table->fetchRowByCurrentHpId($hpId);

        // space hp id をチェック
        if (!$assoc) {
            $assoc = $table->fetchRowBySpaceHpId($hpId);
        }

        // それでもなければfalse
        if (!$assoc) {
            return false;
        }

        return $this->find($assoc->company_id);
    }

    /**
     * 指定 $parent_company_id に紐付いている子companyを取得する
     *
     * @param $parent_company_id
     * @return App\Collections\CustomCollection
     */
    public function fetchAssociatedCompanies($parent_company_id)
    {
        $s = $this->model->select()->withoutGlobalScopes();
        $s->Leftjoin("associated_company", "company.id", "=", "associated_company.subsidiary_company_id");
        $s->select('company.*'); //'associated_company.*'
        $s->where('associated_company.parent_company_id', $parent_company_id);
        $s->where('company.delete_flg', 0);
        $s->where('associated_company.delete_flg', 0);

        // JOINするとSQLエラーが発生するので、一旦論理削除をOFFに
        $rowset = $s->get();

        return $rowset;
    }

    /**
     * 初回公開日の登録
     */
    public function registFirstPublish($publishType, $company_id)
    {
        if ($publishType != config('constants.publish_type.TYPE_PUBLIC')) {
            return false;
        }
        $row = $this->getDataForId($company_id);
        if (empty($row->first_publish_date) || ($row->first_publish_date == '0000-00-00 00:00:00')) {
            $row->first_publish_date = date('Y-m-d H:i:s');
            $row->save();
        }
    }

    /**
     * 【CMS】ログイン失敗時の制御
     * @param $loginRow object
     */
    public function failedLogin($loginRow)
    {
        if (isset($loginRow->login_id)) {
            // 失敗カウントアップ
            $loginRow->login_failed_count = $loginRow->login_failed_count + 1;
            if ($loginRow->login_failed_count >= config('constants.Manager.LOGIN_FAILED_LIMIT')) {
                // アカウントロック
                $loginRow->locked_date = date('Y-m-d H:i:s');
            }
            $loginRow->save();
        }
    }

    /**
     * 【CMS】アカウントのロックを解除する
     * @param $loginRow object
     */
    public function unlockLoginAccount($loginRow)
    {
        $loginRow->login_failed_count = 0;
        $loginRow->locked_date = NULL;
        $loginRow->save();
    }

    public function getCompanyCsv($cols) {
        $this->setAutoLogicalDelete(false);
        $select = $this->model->selectRaw(implode(',', $cols))->withoutGlobalScopes();
        $select->from('company as c');
        $select->leftJoin('second_estate as s', 'c.id', '=', 's.company_id');
        $select->leftJoin('original_setting AS top', function($join) {
            $join->on('c.id', 'top.company_id')->where('top.delete_flg', 0);
        });
        // ATHOME_HP_DEV-4300: Add information FDP
        $select->leftJoin('associated_company_fdp AS fdp', function($join) {
            $join->on('c.id', 'fdp.company_id')->where('fdp.delete_flg', 0);
        });

        $select->where("c.delete_flg", 0);
        return $select;
    }

    public function getCompanySelect($id) {
        $select = $this->model->select();
        $select->where("id", $id);
        $select->where("delete_flg", 0);
        $select->whereRaw("contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') ." || contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE'));

        return $select->first();
    }
}
