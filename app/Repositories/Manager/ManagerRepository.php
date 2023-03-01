<?php

namespace App\Repositories\Manager;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

use function Symfony\Component\Translation\t;

class ManagerRepository extends BaseRepository implements ManagerRepositoryInterface
{
    const LOGIN_FAILED_LIMIT = 10;

    public function getModel()
    {
        return \App\Models\Manager::class;
    }

    public function searchData(Request $request)
    {
        $select = $this->model->select();
        //担当者名
        if ($request->has("name") && $request->name != "") {
            $select = $select->where("name", "like", "%" . $request->name . "%");
        }

        //ログインID
        if ($request->has("login_id") && $request->login_id != "") {
            $select = $select->where("login_id", "like", '%' . $request->login_id . '%');
        }

        //権限
        if ($request->has("privilege_edit_flg") && $request->privilege_edit_flg != "") {
            $select = $select->where("privilege_edit_flg", "=", "1");
        }

        //権限
        if ($request->has("privilege_manage_flg") && $request->privilege_manage_flg != "") {
            $select = $select->where("privilege_manage_flg", "=", "1");
        }

        //権限
        if ($request->has("privilege_create_flg") && $request->privilege_create_flg != "") {
            $select = $select->where("privilege_create_flg", "=", "1");
        }

        //権限
        if ($request->has("privilege_open_flg") && $request->privilege_open_flg != "") {
            $select = $select->where("privilege_open_flg", "=", "1");
        }
        $select->where("id", "!=", 1);

        return $select->orderby('id', 'desc')->paginate(20);
    }

    public function fetchLoginProfile($login_id, $password)
    {
        return $this->model->where('login_id', $login_id)
            ->where('password', $password)->first();
    }

    /**
     * 管理者を取得する
     */
    public function getDataForId($id)
    {
        if (!preg_match("/^[0-9 ]{1,}$/", $id)) {
            throw new Exception("管理者IDに数字以外が指定されています");
        }

        return $this->find($id);
    }
    /**
     * 管理者を取得する
     */
    public function getDataForLoginId($login_id, $id = null)
    {
        $where = [
            ["login_id", $login_id]
        ];
        if ($id) $where[] = ["id", '!=', $id];

        return $this->fetchAll($where);
    }

    /**
     * 【契約管理】ログイン失敗時の制御
     * @param $loginRow object
     */
    public function failedLogin($loginRow)
    {
        if (isset($loginRow->login_id)) {
            // 失敗カウントアップ
            $loginRow->login_failed_count = $loginRow->login_failed_count + 1;
            if ($loginRow->login_failed_count >= self::LOGIN_FAILED_LIMIT) {
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
    public function unlockLoginAccount($loginRow)
    {
        $loginRow->login_failed_count = 0;
        $loginRow->locked_date = NULL;
        $loginRow->save();
    }

    /**
     * 【制作代行】ログイン失敗時の制御
     * @param $creatorRow object
     */
    public function creatorLoginFailed($creatorRow)
    {   
        if (isset($creatorRow)) {
            if (count($creatorRow->toArray()) > 0 && isset($creatorRow->login_id)) {
                // 失敗カウントアップ
                $creatorRow->creator_login_failed_count = $creatorRow->creator_login_failed_count + 1;
                if ($creatorRow->creator_login_failed_count >= self::LOGIN_FAILED_LIMIT) {
                    // アカウントロック
                    $creatorRow->creator_locked_date = date('Y-m-d H:i:s');
                }
                $creatorRow->save();
            }
        }
    }

    /**
     * 【制作代行】アカウントロックを解除する
     * @param $creatorRow object
     */
    public function creatorUnlockLoginAccount($creatorRow)
    {
        $creatorRow->creator_login_failed_count = 0;
        $creatorRow->creator_locked_date = NULL;
        $creatorRow->save();
    }
}
