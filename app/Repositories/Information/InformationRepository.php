<?php

namespace App\Repositories\Information;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use function Symfony\Component\Translation\t;

class InformationRepository extends BaseRepository implements InformationRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\Information::class;
    }

    /**
     * �擾����
     */

    public function getDataForId($id)
    {
        $select = $this->model->select();
        $select = $select->where("id", $id);
        return $select->first();
    }

    public function searchData(Request $request, $search_form, $params)
    {
        $select = $this->model->select();
        if ($search_form->isValid($params)) {

            if ($request->has("title") && $request->title != "") {
                $select = $select->where("title", "like", "%" . $request->title . "%");
            }

            //公開区分
            if ($request->has("display_page_code") && $request->display_page_code != "") {
                $select = $select->where("display_page_code", "=", $request->display_page_code);
            }

            //開始
            if ($request->has("start_date") && $request->start_date != "") {
                $select = $select->where("start_date", ">=", $request->input("start_date") . " 00:00:00");
            }

            //終了
            if ($request->has("end_date") && $request->end_date != "") {
                $select = $select->where("end_date", "!=", '0000-00-00')->where("end_date", "<=", $request->input("end_date") . " 23:59:59");
            }
        }

        return $select->orderby('id', 'desc')->paginate(20);
    }

    public function pagination()
    {
        $select = $this->model->select();
        $select = $this->getLoginbeforeDataStatement();
        $select = $select->where(function ($query) {
            $query->where("display_page_code", 3)
                ->orWhere("display_page_code", 4);
        });
        $select->limit(10);

        return $select->paginate(10);
    }

    public function paginationBeforeLogin()
    {
        $select = $this->getLoginbeforeDataStatement();
        $select = $select->where(function ($query) {
            $query->where("display_page_code", 2)
                ->orWhere("display_page_code", 4);
        });
        $select->limit(5);
        return $select->paginate(10);
    }


    /**
     * ���m�点�i���O�C���O�j�p�̏����擾����
     */
    public function getLoginbeforeData()
    {
        $select = $this->getLoginbeforeDataStatement();
        $select = $select->where(function ($query) {
            $query->where("display_page_code", 2)
                ->orWhere("display_page_code", 4);
        });
        $select->limit(5);
        return $select->get();
    }

    public function getLoginbeforeDataStatement()
    {
        $select = $this->model->where(DB::raw("(DATE_FORMAT(start_date, '%Y-%m-%d'))"), "<=", DB::raw('CURDATE()'));
        $select = $select->where(function ($query) {
            $query->where(DB::raw("(DATE_FORMAT(end_date, '%Y-%m-%d'))"), ">=", DB::raw('CURDATE()'))
                ->orWhere("end_date", "0000-00-00 00:00:00")
                ->orWhereNull('end_date');
        });
        $select->orderBy('start_date', 'desc');
        $select->orderBy('id', 'desc');

        return $select;
    }

    /**
     * ���m�点�i���O�C����j�p�̏����擾����
     */
    public function getLoginafterData()
    {
        $select = $this->model->where(DB::raw("(DATE_FORMAT(start_date, '%Y-%m-%d'))"), "<=", DB::raw('CURDATE()'));
        $select = $select->where(function ($query) {
            $query->where(DB::raw("(DATE_FORMAT(end_date, '%Y-%m-%d'))"), ">=", DB::raw('CURDATE()'))
                ->orWhere("end_date", "0000-00-00 00:00:00")
                ->orWhereNull('end_date');
        });
        $select = $select->where(function ($query) {
            $query->where("display_page_code", 3)
                ->orWhere("display_page_code", 4);
        });
        $select = $select->where("important_flg", "1");
        $select->orderBy('start_date', 'desc');
        $select->orderBy('id', 'desc');

        return $select->get();
    }
}
