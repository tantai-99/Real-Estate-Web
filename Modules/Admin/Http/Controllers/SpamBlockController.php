<?php
namespace Modules\Admin\Http\Controllers;

use App;
use Exception;
use DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\SpamBlock\SpamBlockRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\CompanySpamBlock\CompanySpamBlockRepositoryInterface;
use Modules\Admin\Http\Form\SpamBlockSearch;
use Modules\Admin\Http\Form\SpamBlockRegist;

class SpamBlockController extends Controller
{
    public function init($request, $next)
    {
        $this->view->topicPath('迷惑メール条件管理', "index", 'spamblock');
        return $next($request);
    }

    public function index(Request $request) {
        $params = $request->all();

        $form = new SpamBlockSearch();
        $form->setData($params);
        $this->view->searchForm = $form;

        $spamBlockObj = App::make(SpamBlockRepositoryInterface::class);
        $searchParam = [];
        if ($request->isMethod('post')) {
            if ($form->isValid($params)) {
                if ($request->range_option !== null) {
                    $searchParam[] = ['range_option', $request->range_option];
                }
                if (!empty($request->email)) {
                    $searchParam[] = ['email', 'like', '%' . $request->email . '%'];
                }
                if (!empty($request->tel)) {
                    $searchParam[] = ['tel', 'like', '%' . $request->tel . '%'];
                }
            }
        }
        $spamBlocks = $spamBlockObj->fetchAll($searchParam)->toArray();
        if (!empty($request->member_no)) {
            $memberNo = $request->member_no;
            foreach ($spamBlocks as $key => $spamBlock) {
                if ($spamBlock['range_option'] === config('constants.spamblock.ALL_MEMBER')) {
                    continue;
                }
                if (!$spamBlockObj->isTargetMember($spamBlock['id'], $memberNo)) {
                    unset($spamBlocks[$key]);
                }
            }
        }
        $this->view->spamBlocks = $spamBlocks;
        return view('admin::spamblock.index');
    }

    public function edit(Request $request) {
        $form = new SpamBlockRegist();
        $params = $request->all();

        $form->setData($params);
        if ($request->has("conf") && $request->conf != "") {
            if ($form->isValid($params)) {
                $this->view->topicPath("迷惑メール条件管理確認");
                unset($params['conf']);
                if ($params['email'] === '') {
                    $params['email_option'] = null;
                }
                if ($params['range_option'] === '0') {
                    $params['member_no'] = null;
                }
                $form->setData($params);
                $this->view->form = $form;
                return view('admin::spamblock.conf');
            }
            //戻るボタン押下時
        }

        if ($request->has("back") && $request->back != "") {
            unset($params['back']);
            if ($request->has("email_option") && $request->email_option === "") {
                unset($params['email_option']);
            }
            $form->setData($params);
            $this->view->form = $form;
        } else if ($request->has("add") && $request->add != "") {
            if ($form->isValid($params)) {
                if (empty($params['member_no'])) {
                    $params['member_no'] = $params["member_no_add"];
                } else {
                    $params['member_no'] = $params['member_no'] . ',' . $params["member_no_add"];
                }
                $params['member_no_add'] = '';
            }
            $form->setData($params);
            $this->view->form = $form;
        } else if ($request->has("submit") && $request->submit != "") {
            $this->resist($request);
            return view('admin::spamblock.comp');
            //初期データ取得時
        } else if ($request->has("id") && $request->id != "") {
            if ($params['id'] > 0) {
                $spamBlockObj = App::make(SpamBlockRepositoryInterface::class);
                $spamBlock = $spamBlockObj->getDataForId($params['id']);
                if ($spamBlock == null) {
                    throw new Exception("No SpamBlock Data. ");
                    exit;
                }
                $spamBlockView = [
                    'id' => $spamBlock->id,
                    'range_option' => $spamBlock->range_option,
                    'email' => $spamBlock->email,
                    'email_option' => $spamBlock->email_option,
                    'tel' => $spamBlock->tel
                ];
                if ($spamBlock->range_option === config('constants.spamblock.SPECIFIC_MEMBER')) {
                    $companies = $spamBlockObj->getCompanyListById($params['id']);
                    $memberNoList = [];
                    foreach ($companies as $company) {
                        array_push($memberNoList, $company->member_no);
                    }
                    $spamBlockView['member_no'] = implode(',', $memberNoList);
                }
                $form->setData($spamBlockView);
                $this->view->form = $form;
            }
        }
        $this->view->topicPath("迷惑メール条件管理編集");
        $this->view->form = $form;
        return view('admin::spamblock.edit');
    }

    public function resist($request) {
        $this->view->topicPath("迷惑メール条件管理登録完了");
        $this->view->actionName = '登録';
        $param = [];

        if ($request->has("range_option") && $request->range_option != "") {
            $param['range_option'] = $request->range_option;
        }
        if ($request->has("email")) {
            $param['email'] = $request->email === "" ? null : $request->email;
        }
        if ($request->has("email_option") && $request->email_option != "") {
            $param['email_option'] = $request->email_option;
        }
        if ($request->has("tel")) {
            $param['tel'] = $request->tel === "" ? null : $request->tel;
        }
        $spamBlock = App::make(SpamBlockRepositoryInterface::class);
        $company = App::make(CompanyRepositoryInterface::class);
        $companySpamBlock = App::make(CompanySpamBlockRepositoryInterface::class);
        $companies = array();
        if ($request->has("range_option") && $request->range_option == config('constants.spamblock.SPECIFIC_MEMBER')) {
            $memberNoList = explode(',', $request->member_no);
            $companies = $company->fetchAll(['whereIn' =>['member_no', $memberNoList]]);
        }

        try {
            DB::beginTransaction();
            $spamBlockId = $spamBlock->createOrUpdate($request->id, $param);
            if ($request->has("id") && $request->id != "") {
                $companySpamBlock->delete([['spam_block_id', $request->id]]);
            }
            foreach ($companies as $company) {
                $companySpamBlock->create([
                    'company_id' => $company->id,
                    'spam_block_id' => $spamBlockId
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('迷惑メール条件管理登録に失敗しました。');
        }
    }

    public function delete(Request $request) {
        $this->view->actionName = '削除';
        $params = $request->all();
        $spamBlockObj = App::make(SpamBlockRepositoryInterface::class);
        $companySpamBlockObj = App::make(CompanySpamBlockRepositoryInterface::class);

        if ($request->has("submit") && $request->submit != "") {
            try {
                DB::beginTransaction();
                $spamBlockObj->delete($params['id']);
                $companySpamBlockObj->delete([['spam_block_id', $params['id']]]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw new Exception('迷惑メール条件管理削除に失敗しました。');
            }

            $this->view->topicPath("迷惑メール条件管理削除完了");

            return view('admin::spamblock.comp');
        }

        $this->view->topicPath("迷惑メール条件管理削除確認");
        $form = new SpamBlockRegist();

        if ($request->has("id") && $request->id != "") {
            $spamBlock = $spamBlockObj->getDataForId($params['id']);
            if (is_null($spamBlock)) {
                throw new Exception("No SpamBlock Data. ");
                exit;
            }
            $spamBlockView = [
                'id' => $spamBlock->id,
                'range_option' => $spamBlock->range_option,
                'email' => $spamBlock->email,
                'email_option' => $spamBlock->email_option,
                'tel' => $spamBlock->tel
            ];
            if ($spamBlock->range_option === config('constants.spamblock.PARTIAL_MATCH')) {
                $companies = $spamBlockObj->getCompanyListById($params['id']);
                $memberNoList = [];
                foreach ($companies as $company) {
                    array_push($memberNoList, $company->member_no);
                }
                $spamBlockView['member_no'] = implode(',', $memberNoList);
            }
            $form->setData($spamBlockView);
            $this->view->form = $form;
        }
        return view('admin::spamblock.delete');
    }
}