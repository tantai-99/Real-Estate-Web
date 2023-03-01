<?php
namespace App\Repositories\CompanyAccount;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;

class CompanyAccountRepository extends BaseRepository implements CompanyAccountRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\CompanyAccount::class;
    }

    public function fetchLoginProfile($login_id, $password) {
        return $this->model->where('login_id', $login_id)
                            ->where('password', $password)
                            ->first();
    }

    /**
     * 管理者を取得する
     */
    public function getDataForId($id) {
        if(!preg_match("/^[0-9 ]{1,}$/", $id)) {
            throw new Exception("管理者IDに数字以外が指定されています");
        }

        return $this->model->where(array(
            'id' => $id,
        ))->get();
    }

    public function getDataForCompanyId($id) {

        $select = $this->model->select();
        $select->where('company_id', $id);
        return $select->get();

    }

    /**
     * 契約者IDで取得（1レコード）
     */
    public function getDataRowForCompanyId($id) {
        $select = $this->model->select();
        $select->where('company_id', $id);
        return $select->first();
    }

    /**
     * 管理者を取得する
     */
    public function getDataForLoginId($login_id, $id=0) {

        $where = array(
            ["login_id", $login_id]
        );
        if($id > 0) {
            $where[] = ['id', '!=', $id];
        }
        return $this->model->where($where)->first();
    }

    /**
     * 【契約管理】ログイン失敗時の制御
     * @param $loginRow object
     */
    public function failedLogin($loginRow) {
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
     * 【契約管理】アカウントのロックを解除する
     * @param $loginRow object
     */
    public function unlockLoginAccount($loginRow) {
        $loginRow->login_failed_count = 0;
        $loginRow->locked_date = NULL;
        $loginRow->save();
    }

    /**
     * 【制作代行】ログイン失敗時の制御
     * @param $creatorRow object
     */
    public function creatorLoginFailed($creatorRow) {
        if (count($creatorRow) > 0 && isset($creatorRow->login_id)) {
            // 失敗カウントアップ
            $creatorRow->creator_login_failed_count = $creatorRow->creator_login_failed_count + 1;
            if ($creatorRow->creator_login_failed_count >= config('constants.Manager.LOGIN_FAILED_LIMIT')) {
                // アカウントロック
                $creatorRow->creator_locked_date = date('Y-m-d H:i:s');
            }
            $creatorRow->save();
        }
    }

    /**
     * 【制作代行】アカウントロックを解除する
     * @param $creatorRow object
     */
    public function creatorUnlockLoginAccount($creatorRow) {
        $creatorRow->creator_login_failed_count = 0;
        $creatorRow->creator_locked_date = NULL;
        $creatorRow->save();
    }
}
