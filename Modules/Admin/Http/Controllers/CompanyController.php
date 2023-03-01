<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Library\Custom\Form;
use Library\Custom\Ftp;
use Library\Custom\Estate\Group;
use Illuminate\Support\Facades\App;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use Modules\Admin\Http\Form\CompanySearch;
use Library\Custom\User\Admin;
use Illuminate\Support\Facades\DB;
use App\Repositories\Manager\ManagerRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\AssociatedCompanyFdp\AssociatedCompanyFdpRepositoryInterface;
use App\Repositories\AssociatedCompany\AssociatedCompanyRepositoryInterface;
use App\Repositories\LogDelete\LogDeleteRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\SecondEstate\SecondEstateRepositoryInterface;
use App\Repositories\Tag\TagRepositoryInterface;
use App\Repositories\EstateTag\EstateTagRepositoryInterface;
use App\Repositories\EstateRequestTag\EstateRequestTagRepositoryInterface;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;
use App\Repositories\LogInitializeCms\LogInitializeCmsRepositoryInterface;

use Modules\Admin\Http\Form\CompanyStatus;
use Modules\Admin\Http\Form\BasicInfo;
use Modules\Admin\Http\Form\ContractReserveInfo;
use Modules\Admin\Http\Form\ContractCancelInfo;
use Modules\Admin\Http\Form\CompanyRegistControlPanel;
use Modules\Admin\Http\Form\CompanyRegistCms;
use Modules\Admin\Http\Form\CompanyRegistFtp;
use Modules\Admin\Http\Form\CompanyRegistOther;
use Modules\Admin\Http\Form\CompanyRegistPublishNotify;
use Modules\Admin\Http\Form\CompanyFdpApply;
use Modules\Admin\Http\Form\CompanySecondEstate;
use Modules\Admin\Http\Form\CompanySecondEstateArea;
use Modules\Admin\Http\Form\SecondEstateOther;
use Modules\Admin\Http\Form\CompanyGoogleAnalyticsTag;
use Modules\Admin\Http\Form\CompanyTag;
use Modules\Admin\Http\Form\CompanyEstateTag;
use Modules\Admin\Http\Form\CompanyEstateRequestTag;
use Library\Custom\Model\Estate\FdpType;
use Library\Custom\Model\Lists\CompanyAgreementType;
use Library\Custom\Model\Lists\CmsPlan;
use Library\Custom\Model\Lists\FtpPasvMode;
use Library\Custom\Model\Lists\Original;
use Library\Custom\Model\Lists\CompanyCsvDownloadHeader;
use Library\Custom\Model\Estate\PrefCodeList;
use Library\Custom\Crypt\ApiKey;
use Library\Custom\Plan\ChangeCms;
use Library\Custom\Kaiin;
use DateTime;
use DateTimeZone;
use Exception;
use stdClass;
use Mpdf\Mpdf;
use App\Repositories\OriginalSetting\OriginalSettingRepositoryInterface;
use Library\Custom\DirectoryIterator;
use App\Traits\JsonResponse;
use Modules\Admin\Http\Form\TopGlobalNavigation;
use Modules\Admin\Http\Form\TopFreewordSetting;
use Library\Custom\Model\Lists\TagOriginal;
use Modules\Admin\Http\Form\TopHousingBlock;
use Modules\Admin\Http\Form\TopNotificationSetting;
use Modules\Admin\Http\Form\TopNotificationForm;
use App\Models\HpMainPart;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Models\AssociatedHpPageAttribute;
use Library\Custom\Hp\Page\Parts\EstateKoma;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Crypt\Password;
use Library\Custom\Crypt\CPPassword;
use Library\Custom\Crypt\FTPPassword;
use Library\Custom\Registry;
use Library\Custom\User\Agency;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;

class CompanyController extends Controller
{
    use JsonResponse;
    protected $_controller = 'company';

    protected $companyRepository;
    protected $managerRepository;
    protected $companyAccountRepository;
    protected $associatedCompanyHpRepository;
    protected $associatedCompanyRepository;
    protected $hpRepository;
    protected $releaseScheduleRepository;
    protected $secondEstateRepository;
    protected $tagRepository;
    protected $hpPageRepository;
    protected $estateTagRepository;
    protected $estateRequestTagRepository;
    protected $logInitializeCmsRepository;

    const INITIALIZE_DB_ROWS = false;

    public function init($request, $next)
    {
        $this->companyRepository = App::make(CompanyRepositoryInterface::class);
        $this->managerRepository = App::make(ManagerRepositoryInterface::class);
        $this->companyAccountRepository = App::make(CompanyAccountRepositoryInterface::class);
        $this->associatedCompanyHpRepository = App::make(AssociatedCompanyHpRepositoryInterface::class);
        $this->hpRepository = App::make(HpRepositoryInterface::class);
        $this->releaseScheduleRepository = App::make(ReleaseScheduleRepositoryInterface::class);
        $this->secondEstateRepository = App::make(SecondEstateRepositoryInterface::class);
        $this->associatedCompanyRepository = App::make(AssociatedCompanyRepositoryInterface::class);
        $this->originalSettingRepository = App::make(OriginalSettingRepositoryInterface::class);
        $this->tagRepository = App::make(TagRepositoryInterface::class);
        // $this->hpPageRepository = App::make(HpPageRepositoryInterface::class);
        $this->estateTagRepository = App::make(EstateTagRepositoryInterface::class);
        $this->estateRequestTagRepository = App::make(EstateRequestTagRepositoryInterface::class);
        $this->estateAssociatedCompanyRepository = App::make(EstateAssociatedCompanyRepositoryInterface::class);
        $this->logInitializeCmsRepository = App::make(LogInitializeCmsRepositoryInterface::class);
        return $next($request);
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $this->view->topicPath("契約管理");
        $this->view->search_form = $search_form = new CompanySearch();

        $cmpAgreTypeObj = new CompanyAgreementType();
        $this->view->company_list = $cmpAgreTypeObj->getAll();
        $this->view->agency = getInstanceUser('admin')->isAgency() && !$this->checkPrivilegeEdit(getInstanceUser('admin')->getProfile()->id);

        $params = $request->all();
        $search_form->setData($params);
        $rows = $this->companyRepository->searchData($request, $search_form, $params);
        $this->view->company = $rows;
        $search_arr = array();
        foreach ($search_form->getElements() as $key => $val) {
            $search_arr[$key] = ($val->getValue() == null) ? "" :  $val->getValue();
            // echo $search_arr[$key];
        }
        $this->view->search_param = $search_arr;
        return view('admin::company.index');
    }

    public function edit(Request $request)
    {
        $this->checkUserRules();

        $this->view->topicPath('契約管理', "index", $this->_controller);
        $this->view->topicPath("契約者登録");

        //設定系の情報取得
        $company_config = getConfigs('admin.company');
        //API系のURIなど
        $defailt_backbone = $company_config->backbone->api;
        $this->view->backbone = $defailt_backbone;
        //ＦＴＰ
        $this->view->default_ftp = $company_config->company->ftp;
        //コントロールパネル
        $this->view->default_cp  = $company_config->company->controlpanel;

        //パラメータ取得
        $params = $request->all();
        $this->view->params = $params;

        $row = array();

        if ($request->has('id') && ($params['id'] > 0)) {
            $row = $this->companyRepository->getDataForId($params['id']);
            if ($row == null) {
                throw new Exception("No Company Data. ");
                exit;
            }
        }

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyStatus($row), 'status');
        $form->addSubForm(new BasicInfo(), 'basic');
        $form->addSubForm(new ContractReserveInfo($row), 'reserve');
        $form->addSubForm(new ContractCancelInfo(), 'cancel');
        $form->addSubForm(new CompanyRegistControlPanel(), 'cp');
        $form->addSubForm(new CompanyRegistCms(), 'cms');
        $form->addSubForm(new CompanyRegistFtp(), 'ftp');
        $form->addSubForm(new CompanyRegistOther(), 'other');
        $form->addSubForm(new CompanyRegistPublishNotify(), 'pn');
        $companyId = empty($row->id) ? '' : $row->id;
        //form edit (company/detail)
        $fdpDisplay = false;
        if (!empty($row->cms_plan)) {
            $fdpDisplay = $row->cms_plan != config('constants.cms_plan.CMS_PLAN_LITE');
        } else if ($request->has('status')) {
            $fdpDisplay = $params['status']['cms_plan'] != config('constants.cms_plan.CMS_PLAN_LITE');
        }
        if (empty($request->has('id'))) {
            $this->view->fdp_display = false;
        }
        if ($request->has('basic') && empty($params['basic']['id'])) {
            $this->view->fdp_display = false;
        } else {
            // ATHOME_HP_DEV-4562: Check FDP information is displayed
            if ($request->has('copy') && $params['copy']) {
                $this->view->fdp_display = false;
            } else {
                $this->view->fdp_display = $fdpDisplay;
            }
            if (!$companyId) {
                $companyId = empty($params['basic']['id']) ? '' : $params['basic']['id'];
            }
        }
        $riyo = array('riyoStart' => '', 'riyoStop' => '');
        if ($companyId) {
            $riyo = FdpType::getInstance()->setDateFormFDP($companyId);
        }
        $form->addSubForm(new CompanyFdpApply($riyo), 'fdp');

        //契約内容
        $cmpAgreTypeObj = new CompanyAgreementType();
        $this->view->company_agree_list = $cmpAgreTypeObj->getAll();

        // プラン
        $cmpCmsPlanObj    = new CmsPlan();
        $this->view->cms_plan_list = $cmpCmsPlanObj->getAll();

        //PASVモード
        $ftpPasvObj = new FtpPasvMode();
        $this->view->pasv = $ftpPasvObj->getAll();

        //登録ボタン押下時
        if ($request->has("asd") && $request->asd != "") {
            //契約が「評価・分析のみ契約」の場合は必須を外す
            if (isset($params['basic']['contract_type']) && $params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')) {
                foreach ($form->getSubForms() as $name => $val) {
                    foreach ($form->getSubForm($name)->getElements() as $key => $element) {
                        if (!in_array($key, array("member_no", "member_name", "login_id", "password"))) {
                            $element->setRequired(false);
                        }
                    }
                }
            }

            //バリデーション
            $form->setData($params);
            if ($form->isValid($params)) {
                $topic = '<img alt="ホーム" src="/images/common/icon_home.png">';
                $this->view->topicPath()->clear();
                $this->view->topicPath($topic, 'index', 'index', array(), false);
                $this->view->topicPath('契約管理', "index", $this->_controller);
                $this->view->topicPath("契約者登録確認");
                $error_flg = false;

                //既に契約店Noがある場合
                $rows = $this->companyRepository->getDataForMemberNo($params['basic']["member_no"], $params['basic']["id"]);
                if ($rows != null && $rows->count() > 0) {
                    $form->getSubForm('basic')->getElement('member_no')->setMessages(array("既に加盟店Noが使用されています。"));
                    $error_flg = true;
                }

                //CMSのログインIDが登録されている場合
                $rows = $this->companyAccountRepository->getDataForLoginId($params['cms']["login_id"], $params['cms']["account_id"]);
                if ($rows != null && $rows->count() > 0) {
                    $form->getSubForm('cms')->getElement('login_id')->setMessages(array("既にCMSログインIDが使用されています。"));
                    $error_flg = true;
                }

                //ドメインチェック
                if ($params['basic']["domain"] != "") {
                    $rows = $this->companyRepository->getDataForDomain($params['basic']["domain"], $params['basic']["id"]);
                    if ($rows != null && $rows->count() > 0) {
                        $form->getSubForm('basic')->getElement('domain')->setMessages(array("既にこのドメインは使用されています。"));
                        $error_flg = true;
                    }
                }

                //「評価・分析のみ契約」の場合
                if ($params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')) {
                    //利用開始日が入って無いのに利用停止日が入っている場合はエラー
                    if ((isset($params['basic']['reserve_start_date']) == false) && isset($params['basic']['end_date'])) {
                        $form->getSubForm('basic')->getElement('end_date')->setMessages(array("利用開始日が設定されていない場合は、利用停止日を設定できません。"));
                        $error_flg = true;

                        //利用開始日が入っているのに利用停止日が入ってない場合はエラー
                    } else if (isset($params['basic']['reserve_start_date']) && (isset($params['basic']['end_date']) == false)) {
                        $form->getSubForm('basic')->getElement('end_date')->setMessages(array("既に本契約されている場合は、利用停止日を設定しなければなりません。"));
                        $error_flg = true;
                    }
                }

                //利用日チェック
                if ($params['reserve']['reserve_applied_start_date'] != "" && $params['reserve']['reserve_start_date'] != "") {
                    $reserve_applied_start_date    = str_replace(array("-", "/"), "", $params['reserve']['reserve_applied_start_date']);
                    $reserve_start_date            = str_replace(array("-", "/"), "", $params['reserve']['reserve_start_date']);
                    if ($reserve_applied_start_date > $reserve_start_date) {
                        $form->getSubForm('reserve')->getElement('reserve_applied_start_date')->setMessages(array("利用開始申請日は、利用開始日より過去日を設定してください。"));
                        $error_flg = true;
                    }
                }

                //利用日チェック
                if ($params['cancel']['applied_end_date'] != "" && $params['cancel']['end_date'] != "") {
                    $applied_end_date = str_replace(array("-", "/"), "", $params['cancel']['applied_end_date']);
                    $end_date = str_replace(array("-", "/"), "", $params['cancel']['end_date']);
                    if ($applied_end_date > $end_date) {
                        $form->getSubForm('cancel')->getElement('applied_end_date')->setMessages(array("利用停止申請日は、利用停止日より過去日を設定してください。"));
                        $error_flg = true;
                    }
                }

                //利用開始日と利用停止日のチェック
                if (($params['reserve']['reserve_start_date'] != "") && ($params['cancel']['end_date'] != "")) {
                    if ($form->getSubForm('status')->getContractSatus() != 'off') {    // no validate at recontract
                        $start    = str_replace(array("-", "/"), "", $params['reserve']['reserve_start_date']);
                        $end    = str_replace(array("-", "/"), "", $params['cancel']['end_date']);
                        if ($start > $end) {
                            $form->getSubForm('cancel')->getElement('end_date')->setMessages(array("利用停止日は、利用開始日より未来日を設定してください。"));
                            $error_flg = true;
                        }
                    }
                }

                // 初回利用開始日と利用停止日のチェック
                if (isset($params['status'])) {
                    if (($params['status']['initial_start_date'] != "") && ($params['cancel']['end_date'] != "")) {
                        $start    = str_replace(array("-", "/"), "", $params['status']['initial_start_date']);
                        $end    = str_replace(array("-", "/"), "", $params['cancel']['end_date']);
                        if ($start > $end) {
                            $form->getSubForm('cancel')->getElement('end_date')->setMessages(array("利用停止日は、初回利用開始日より未来日を設定してください。"));
                            $error_flg    = true;
                        }
                    }

                    //現在の契約情報の契約担当者ID
                    if ($params['status']['contract_staff_id'] != "" && ($params['status']['contract_staff_name'] == "" || $params['status']['contract_staff_department'] == "")) {
                        $form->getSubForm('status')->getElement('contract_staff_name')->setMessages(array("契約担当者名が設定されていません。参照ボタンより取得してください。"));
                        $error_flg = true;
                    } else if ($params['status']['contract_staff_id'] == "" && ($params['status']['contract_staff_name'] != "" || $params['status']['contract_staff_department'] != "")) {
                        $form->getSubForm('status')->getElement('contract_staff_id')->setMessages(array("契約担当者が設定されていません。"));
                        $error_flg = true;
                    }
                }

                //契約担当者系の設定
                if ($params['reserve']['reserve_contract_staff_id'] != "" && ($params['reserve']['reserve_contract_staff_name'] == "" || $params['reserve']['reserve_contract_staff_department'] == "")) {
                    $form->getSubForm('reserve')->getElement('reserve_contract_staff_name')->setMessages(array("契約担当者名が設定されていません。参照ボタンより取得してください。"));
                    $error_flg = true;
                } else if ($params['reserve']['reserve_contract_staff_id'] == "" && ($params['reserve']['reserve_contract_staff_name'] != "" || $params['reserve']['reserve_contract_staff_department'] != "")) {
                    $form->getSubForm('reserve')->getElement('reserve_contract_staff_id')->setMessages(array("契約担当者が設定されていません。"));
                    $error_flg = true;
                }

                //解約担当者系の設定
                if ($params['cancel']['cancel_staff_id'] != "" && ($params['cancel']['cancel_staff_name'] == "" || $params['cancel']['cancel_staff_department'] == "")) {
                    $form->getSubForm('cancel')->getElement('cancel_staff_name')->setMessages(array("解約担当者名が設定されていません。参照ボタンより取得してください。"));
                    $error_flg = true;
                } else if ($params['cancel']['cancel_staff_id'] == "" && ($params['cancel']['cancel_staff_name'] != "" || $params['cancel']['cancel_staff_department'] != "")) {
                    $form->getSubForm('cancel')->getElement('cancel_staff_id')->setMessages(array("解約担当者が設定されていません。"));
                    $error_flg = true;
                }
                // ATHOME_HP_DEV-2447 【プラン変更】地図検索の解約情報の未入力アラート
                if (
                    (isset($row['map_start_date'])) &&
                    (isset($row['map_end_date'])) &&
                    ($row->map_start_date                              != "") &&
                    ($row->map_end_date                              == "") &&
                    ($params['reserve']['reserve_cms_plan'] == config('constants.cms_plan.CMS_PLAN_ADVANCE'))
                ) {
                    $form->getSubForm('reserve')->getElement('reserve_cms_plan')->setMessages(array("「地図検索設定」の停止処理が完了してません。"));
                    $error_flg = true;
                }
                if (
                    (isset($row['map_start_date'])) &&
                    (isset($row['map_end_date'])) &&
                    ($row->map_start_date                              != "") &&
                    ($row->map_end_date                              == "") &&
                    ($params['cancel']['end_date'] != "")
                ) {
                    $form->getSubForm('cancel')->getElement('end_date')->setMessages(array("「地図検索設定」の停止処理が完了してません。"));
                    $error_flg = true;
                }
                $secondEstateRow    = $this->secondEstateRepository->getDataForCompanyId($params['id']);
                if (
                    ($secondEstateRow                                  != null) &&
                    ($secondEstateRow->start_date                  != "") &&
                    ($secondEstateRow->end_date                      == "") &&
                    ($params['cancel']['end_date'] != "")
                ) {
                    $form->getSubForm('cancel')->getElement('end_date')->setMessages(array("「2次広告自動公開設定」の停止処理が完了してません。"));
                    $error_flg = true;
                }
                if (!$error_flg) {
                    //submit削除
                    //評価分析のときは登録しない
                    if (
                        $params['basic']["id"] == null &&
                        isset($params['basic']['contract_type']) && $params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')
                    ) {
                        $form->getSubForm('ftp')->getElement('ftp_server_name')->setValue("");
                        $form->getSubForm('ftp')->getElement('ftp_server_port')->setValue("");
                        $form->getSubForm('ftp')->getElement('ftp_user_id')->setValue("");
                        $form->getSubForm('ftp')->getElement('ftp_password')->setValue("");
                        $form->getSubForm('ftp')->getElement('ftp_directory')->setValue("");
                        $form->getSubForm('cp')->getElement('cp_url')->setValue("");
                    }
                    $request->input("asd", "");
                    $request->input("back", "");
                    return view('admin::company.conf');
                }
            }

            //チェックが終わったら、必須系を戻す（見た目が気持ち悪い感じになるので）
            foreach ($form->getSubForms() as $name => $val) {
                foreach ($val->getElements() as $key => $element) {
                    if (!in_array($key, array('applied_end_date', 'end_date', 'cancel_staff_id', 'cancel_staff_name', 'cancel_staff_department', 'remarks'))) $element->setRequired(true);
                }
            }
            //戻るボタン押下時
        } else if ($request->has("submit_regist") && $request->submit_regist != "") {    //登録ボタン押下時

            getUser()->clearCsrfToken();

            $conf_error_str = array();
            //再度チェック（既に契約店Noがある場合）
            $rows = $this->companyRepository->getDataForMemberNo($params['basic']["member_no"], $params['basic']["id"]);
            if ($rows != null && $rows->count() > 0) {
                // $request->set("conf_error", "true");
                $request->conf_error = true;
                $conf_error_str = array_merge(array("basic" => array("member_no" => "既に加盟店Noが使用されています。")), $conf_error_str);
            }

            //再度チェック（CMSのログインIDが登録されている場合）
            $rows = $this->companyAccountRepository->getDataForLoginId($params['cms']["login_id"], $params['cms']["account_id"]);
            if ($rows != null && $rows->count() > 0) {
                // $this->_setParam("conf_error", "true");
                $request->conf_error = true;
                $conf_error_str =  array_merge(array("cms" => array("login_id" => "既にCMSログインIDが使用されています。")), $conf_error_str);
            }

            if ($request->has("conf_error") && $request->conf_error != "") {
                $request->set("conf_error_str", $conf_error_str);
                return redirect()->route("admin.company.edit");
            }

            DB::beginTransaction();

            //契約者登録
            $copy_from_member_no    = (isset($params['basic']["copy_from_member_no"]) ? $params['basic']["copy_from_member_no"] : null);
            unset($params["module"]);
            unset($params["controller"]);
            unset($params["action"]);
            unset($params["submit"]);
            unset($params['status']["contract_status"]);
            unset($params['status']["cms_plan"]);
            unset($params['basic']["copy_from_member_no"]);
            $companyRow = null;
            //新規
            if (!isset($params['basic']["id"]) || $params['basic']["id"] == "" || $copy_from_member_no) {

                unset($params["id"]);

                $create_arr = array();

                foreach ($params as $name => $arr) {
                    if ($name == "cms") continue;
                    if ($name == "submit_regist") continue;
                    if ($name == "_token") continue;

                    foreach ($arr as $key => $val) {
                        if ($key == "id") continue;
                        if (in_array($key, ['contract_type', 'reserve_cms_plan', 'member_name', 'member_no', 'company_name', 'location']) && is_null($val)) {
                            $val = '';
                        }
                        if ($key == 'cp_password') {
                            $cpPassword = new CPPassword();
                            $val = $cpPassword->encrypt($val);
                        }
                        if ($key == 'ftp_password') {
                            $ftpPassword = new FTPPassword();
                            $val = $ftpPassword->encrypt($val);
                        }
                        $create_arr[$key] = $val;
                    }
                }
                $row = $this->companyRepository->create($create_arr);
                $row->save();
                $id = $row->id;
                $this->_setPlan($params, $row, $copy_from_member_no);

                //  ATHOME_HP_DEV-2235	デモアカウントのHPコピー機能
                if ($copy_from_member_no) {
                    $profile    = $this->companyRepository->fetchLoginProfileByMemberNo($copy_from_member_no);
                    $currentHp    = $profile->getCurrentHp();
                    $newHp        = $currentHp->copyAll();
                    $data                      = array();
                    $data['company_id'] = $id;
                    $data['current_hp_id'] = $newHp->id;
                    $this->associatedCompanyHpRepository->create($data);
                }

                // アカウント登録
                $rows = [];
                $rows['company_id'] = $id;
                $rows['login_id']   = $params['cms']["login_id"];
                $rows['password']   = $params['cms']["password"];
                $password = new Password();
                $rows['password'] = $password->encrypt($rows['password']);
                // APIキーを設定する
                $crypApikey = new ApiKey();
                $rows['api_key'] = $crypApikey->encrypt($id);
                $this->companyAccountRepository->create($rows);

                if ($params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_PRIME')) {    // 新規の本契約なら、そのまま2次広告自動公開設定へ
                    DB::commit();
                    if ($params['reserve']['reserve_cms_plan'] == config('constants.cms_plan.CMS_PLAN_LITE')) {
                        return  redirect('/admin/company/comp/?id=' . $id);
                    }
                    return redirect("/admin/company/second-estate?company_id={$id}");
                }
                $companyRow = $row;
                //更新
            } ////
            else {
                $id = $params['basic']["id"];
                $companyRow = $this->companyRepository->find($id);
                if ($companyRow == null) {
                    throw new Exception("No Company Data.");
                    return;
                }

                $memberBeforeChange = $companyRow->member_no;
                unset($companyRow->delete_flg);
                unset($companyRow->create_id);
                unset($companyRow->create_date);
                unset($companyRow->update_date);

                // ドメインが変更された場合は、全上げフラグを立てる
                if (
                    $params['basic']["domain"] != "" && !empty($companyRow->domain) &&
                    $companyRow->domain != $params['basic']["domain"]
                ) {
                    // hp更新
                    $hpTable = $this->hpRepository;
                    $reserveTable = $this->releaseScheduleRepository;

                    $hp = array();
                    if ($currentRows = $companyRow->getCurrentHp()) {
                        $hp[] = $currentRows;
                    }
                    foreach ($hp as $hprow) {
                        // $hpTable->update(array('all_upload_flg' => 1), array('id = ?' => $hprow->id));
                        // ATHOME_HP_DEV-3126
                        $hprow->all_upload_flg = 1;
                        $hprow->setAllUploadParts('ALL', 1);
                        $hprow->save();

                        // 公開済みの物件検索設定の削除
                        $companyRow->deletePublicSearch();

                        $reserveTable->update(array(['hp_id', $hprow->id]), array('delete_flg' => 1));
                    }
                }

                //契約者更新
                $no_update_arr = array("id", "delete_flg", "create_id", "create_date", "update_id", "update_date");

                foreach ($params as $name => $arr) {
                    if ($name    == '_token') continue;
                    if ($name    == "cms") continue;
                    if ($name    == "submit_regist") continue;
                    foreach ($arr as $key => $val) {
                        if ($key == "id") continue;
                        if ($key == "member_linkno") continue;
                        if (in_array($key, ['contract_type', 'reserve_cms_plan', 'member_name', 'member_no', 'company_name', 'location']) && is_null($val)) {
                            $val = '';
                        }
                        $companyRow->$key = $val;
                    }
                }

                //check top original before
                $checkTopBefore = false;
                if ($companyRow != null && $companyRow->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE')) {
                    $checkTopBefore = $companyRow->checkTopOriginal();
                }
                Registry::set('checkTopBefore',$checkTopBefore);

                $this->_setAutoCancel($companyRow);
                $companyRow->save();
                $this->_setPlan($params, $companyRow);

                //アカウント更新
                $accountRow = $companyRow->companyAccount()->first();
                if ($accountRow == null) {
                    DB::rollback();
                    throw new Exception("No CompanyAccount Data.");
                    return;
                }
                // パスワードが更新されていたら、ログイン試行回数を0にしロックを解除する
                if ($accountRow->password !== $params['cms']["password"]) {
                    $accountRow->login_failed_count = 0;
                    $accountRow->locked_date = NULL;
                }
                $accountRow->login_id   = $params['cms']["login_id"];
                $accountRow->password   = $params['cms']["password"];
                $accountRow->save();
                if ($params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_PRIME')) {
                    DB::commit();
                    if ($companyRow->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') && empty($params['reserve']['reserve_cms_plan'])) {
                        return redirect('/admin/company/comp/?id=' . $companyRow->id);
                    }
                    if ($companyRow->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') && $params['reserve']['reserve_cms_plan'] != config('constants.cms_plan.CMS_PLAN_LITE')) {
                        return redirect("/admin/company/second-estate?company_id={$companyRow->id}");
                    }
                    if ($companyRow->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') || empty($companyRow->cms_plan) && $params['reserve']['reserve_cms_plan'] == config('constants.cms_plan.CMS_PLAN_LITE')) {
                        return redirect('/admin/company/comp/?id=' . $companyRow->id);
                    }
                    return redirect("/admin/company/second-estate?company_id={$companyRow->id}");
                }
            }
            DB::commit();
            if (isset($memberBeforeChange) && ($memberBeforeChange != $companyRow->member_no)) {
                FdpType::getInstance()->updateFdpByMemberNo($companyRow->member_no, $companyRow->cms_plan, $id);
            }

            if ($companyRow->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE')) {
                if (isset($copy_from_member_no) && $copy_from_member_no) {

                    $redirect_url = '/admin/company/comp/?id=' . $id;
                    return redirect($redirect_url);
                    exit;
                }

                return redirect('admin/company/comp/?id=' . $id);
            } else {

                if (isset($copy_from_member_no) && $copy_from_member_no) {
                    $redirect_url = '/admin/company/comp/?id=' . $id;
                    return redirect($redirect_url);
                    exit;
                }

                return redirect('/admin/company/comp/?id=' . $id);
            }
        } else if ($request->has("back") && $request->back != "") {
            unset($params['back']);
            $form->setData($params);

            //確認画面でエラーになった場合
        } else if ($request->has("conf_error") && $request->conf_error != "") {

            $form->setData($params);
            //エラー内容の設定
            foreach ($request->conf_error_str as $name => $data) {
                foreach ($data as $key => $val) {
                    $form->$name->get($key)->addErrors(array($val));
                }
            }
            unset($params['conf_error']);
            unset($params['conf_error_str']);

            //初期データ取得時
        } else if ($request->has("id") && $request->id != "") {

            //契約者情報の取得
            $row = array();
            $row = $this->companyRepository->getDataForId($request->id)->toArray();

            //  ATHOME_HP_DEV-2235	デモアカウントのHPコピー機能
            if (array_key_exists('copy', $params) && ($params['copy'] == 'true')) {
                $form->getSubForm('basic')->getElement('contract_type')->setValue(config('constants.company_agreement_type.CONTRACT_TYPE_DEMO'));
                $form->getSubForm('basic')->getElement('copy_from_member_no')->setValue($row['member_no']);
                $form->getSubForm('cp')->getElement('cp_url')->setValue($row['cp_url']);
                $row = array();
            } else {
                $form->getSubForm('basic')->removeElement('copy_from_member_no');
                //アカウントの取得
                $rows = array();
                $rowsObj = $this->companyAccountRepository->getDataForCompanyId($request->id);
                foreach ($rowsObj as $key => $val) {
                    $rows = $val->toArray();
                    break;
                }
                if (isset($rows) && count($rows) > 0) {
                    if (!isset($rows['account_id'])) {
                        $rows['account_id'] = $rows['id'];
                        unset($rows['id']);
                    }
                    $form->setData($rows);
                }
            }

            if ($row != null) {
                //日付周りの調整
                $date = substr($row['applied_start_date'], 0, 10);
                $row['applied_start_date'] = (($date == "0000-00-00") ? "" : str_replace("-", "/", $date));

                $date = substr($row['start_date'], 0, 10);
                $row['start_date'] = (($date == "0000-00-00") ? "" : str_replace("-", "/", $date));

                $date = substr($row['initial_start_date'], 0, 10);
                $row['initial_start_date'] = (($date == "0000-00-00") ? "" : str_replace("-", "/", $date));

                $date = substr($row['reserve_applied_start_date'], 0, 10);
                $row['reserve_applied_start_date'] = (($date == "0000-00-00") ? "" : str_replace("-", "/", $date));

                $date = substr($row['reserve_start_date'], 0, 10);
                $row['reserve_start_date'] = (($date == "0000-00-00") ? "" : str_replace("-", "/", $date));

                $date = substr($row['applied_end_date'], 0, 10);
                $row['applied_end_date'] = (($date == "0000-00-00") ? "" : str_replace("-", "/", $date));

                $date = substr($row['end_date'], 0, 10);
                $row['end_date'] = (($date == "0000-00-00") ? "" : str_replace("-", "/", $date));

                $date = substr($row['map_applied_start_date'], 0, 10);
                $row['map_applied_start_date'] = (($date == "0000-00-00") ? "" : $date);

                $date = substr($row['map_start_date'], 0, 10);
                $row['map_start_date'] = (($date == "0000-00-00") ? "" : $date);

                $date = substr($row['map_applied_end_date'], 0, 10);
                $row['map_applied_end_date'] = (($date == "0000-00-00") ? "" : $date);

                $date = substr($row['map_end_date'], 0, 10);
                $row['map_end_date'] = (($date == "0000-00-00") ? "" : $date);
            }
            // ATHOME_HP_DEV-5199 :公開処理通知
            $form->getSubForm('pn')->getElement('publish_notify')->setValue(0);

            $form->setData($row);
        } else {

            //デフォルト値を設定していく
            $form->getSubForm('basic')->getElement('contract_type')->setValue(0);
            //ＦＴＰ
            $defailt_ftp = $company_config->company->ftp;
            $form->getSubForm('ftp')->getElement('ftp_server_port')->setValue($defailt_ftp->port);
            //			$form->getSubForm('ftp')->ftp_pasv_flg->setValue(0);
            $form->getSubForm('ftp')->getElement('ftp_server_name')->setValue($defailt_ftp->server_name);
            $form->getSubForm('ftp')->getElement('ftp_password')->setValue($defailt_ftp->password);
            //コントロールパネル
            $defailt_cp = $company_config->company->controlpanel;
            $form->getSubForm('cp')->getElement('cp_url')->setValue($defailt_cp->url);
            // 公開処理通知
            $form->getSubForm('pn')->getElement('publish_notify')->setValue(0);
        }

        return view('admin::company.edit');
    }

    public function conf(Request $request) {
        $this->checkUserRules();
        
    	$this->view->topicPath('契約管理', "index", $this->_controller);
        $this->view->topicPath("契約者登録確認");
        
        //契約内容
        $cmpAgreTypeObj = new CompanyAgreementType();
        $this->view->company_agree_list = $cmpAgreTypeObj->getAll();

        // プラン
        $cmpCmsPlanObj    = new CmsPlan();
        $this->view->cms_plan_list = $cmpCmsPlanObj->getAll();

        //PASVモード
        $ftpPasvObj = new FtpPasvMode();
        $this->view->pasv = $ftpPasvObj->getAll();
        $this->view->params = $request->all();

        // ATHOME_HP_DEV-5105 CSRFトークン生成
        getUser()->regenerateCsrfToken();
        return view('admin::company.conf');
    }
    /**
     * 詳細表示
     */
    public function detail(Request $request)
    {

        $this->view->topicPath('契約管理', "index", $this->_controller);
        $this->view->topicPath("契約者詳細");
        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $companyAccountObj = $this->companyAccountRepository;

        //契約者情報の取得
        $row = $companyObj->getDataForId($request->id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        $company_id = $request->id;
        $isAdmin = getInstanceUser('admin')->checkIsSuperAdmin(getInstanceUser('admin')->getProfile());
        $isAgency = getInstanceUser('admin')->isAgency();
        $isEdit = getInstanceUser('admin')->getProfile()->privilege_edit_flg;
        $isManage = getInstanceUser('admin')->getProfile()->privilege_manage_flg;

        $this->view->original_plan = false;
        $this->view->original_edit = false;
        if ($row->checkTopOriginal() && !$this->checkRedirectTopOriginal($row, $company_id, $isAdmin, $isAgency)) {
            $this->view->original_plan = true;
        } else if ($row->checkTopOriginal() && ($isEdit || ($isEdit && $isManage))) {
            $this->view->original_edit = true;
        }

        // 「修正権限」「代行更新権限」が有無なら初期化ボタン表示
        $dataLogin = $this->managerRepository->getDataForId(getInstanceUser('admin')->getProfile()->id);
        $this->view->initialize_cms = false;
        if ($dataLogin->privilege_edit_flg == 1 || $dataLogin->privilege_open_flg == 1) {
            $this->view->initialize_cms = true;
        }

        $this->view->original_setting_title = Original::getOriginalSettingTitle();
        $this->view->original_edit_title = Original::getOriginalEditTitle();
        $this->view->original_tag = Original::getEffectMeasurementTitle();
        $this->view->current_hp    = $row->getCurrentHp();            // HPがあるかどうかの判断の為
        $this->view->agency = $isAgency && !$this->checkPrivilegeEdit(getInstanceUser('admin')->getProfile()->id);

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyStatus($row), 'status');
        $form->addSubForm(new BasicInfo(), 'basic');
        $form->addSubForm(new ContractReserveInfo(), 'reserve');
        $form->addSubForm(new ContractCancelInfo(), 'cancel');
        $form->addSubForm(new CompanyRegistControlPanel(), 'cp');
        $form->addSubForm(new CompanyRegistCms(), 'cms');
        $form->addSubForm(new CompanyRegistFtp(), 'ftp');
        $form->addSubForm(new CompanyRegistOther(), 'other');
        $form->addSubForm(new CompanyRegistPublishNotify(), 'pn');
        $riyo = FdpType::getInstance()->setDateFormFDP($row->id);
        $form->addSubForm(new CompanyFdpApply($riyo), 'fdp');
        $this->view->cms_plan = $row->cms_plan;
        //契約内容
        $cmpAgreTypeObj = new CompanyAgreementType();
        $this->view->company_agree_list = $cmpAgreTypeObj->getAll();

        // プラン
        $cmpCmsPlanObj    = new CmsPlan();
        $this->view->cms_plan_list = $cmpCmsPlanObj->getAll();

        //PASVモード
        $ftpPasvObj = new FtpPasvMode();
        $this->view->pasv = $ftpPasvObj->getAll();
        //日付周りの設定
        $row->initial_start_date            = $row->initial_start_date_view;
        $row->reserve_applied_start_date    = $row->reserve_applied_start_date_view;
        $row->reserve_start_date            = $row->reserve_start_date_view;
        $row->start_date                    = $row->start_date_view;
        $row->applied_start_date            = $row->applied_start_date_view;
        $row->applied_end_date                = $row->applied_end_date_view;
        $row->end_date                        = $row->end_date_view;
        $row->map_applied_start_date        = $row->map_applied_start_date_view;
        $row->map_start_date                = $row->map_start_date_view;
        $row->map_applied_end_date            = $row->map_applied_end_date_view;
        $row->map_end_date                    = $row->map_end_date_view;
        $row = $row->toArray();

        //インターネットコードの設定  @TODO
        $row['member_linkno'] = $this->getInternetCode($row['member_no']);

        $form->setData($row);

        //アカウントの取得
        $rowsObj = $companyAccountObj->getDataForCompanyId($row['id']);
        if ($rowsObj == null) {
            throw new Exception("No Company Account Data. ");
            exit;
        }
        $rows = array();
        foreach ($rowsObj as $key => $val) {
            $rows = $val->toArray();
            break;
        }

        if (isset($rows) && count($rows) > 0) {
            if (!isset($rows['account_id'])) {
                $rows['account_id'] = $rows['id'];
                unset($rows['id']);
            }
            $this->view->form->setData($rows);
        }
        return view('admin::company.detail');
    }
    /**
     * 会員APIに接続して会員番号に対応するインターネットコードを取得します。
     */
    private function getInternetCode($member_no)
    {
        // 会員番号が設定されていない場合は何も返さない
        if (empty($member_no)) return null;

        // 会員APIに接続して会員情報を取得
        $apiParam = new Kaiin\Kaiin\KaiinParams();
        $apiParam->setKaiinNo($member_no);
        $apiObj = new Kaiin\Kaiin\Kaiin();
        $kaiinData = $apiObj->get($apiParam, '会員基本取得');
        if (is_null($kaiinData) || empty($kaiinData)) {
            return "会員Noに誤りがあります。";
        }

        $kaiinData = (object)$kaiinData;
        if (!property_exists($kaiinData, 'kaiinLinkNo') || empty($kaiinData->kaiinLinkNo)) {
            return "インターネットコードが設定されていません。";
        }
        return $kaiinData->kaiinLinkNo;
    }

    public function delete(Request $request)
    {
        if (!$request->has("company_id") || $request->company_id == "" || !is_numeric($request->company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }
        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($request->company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }
        $data = array();
        $data['delete_flg'] = 1;
        DB::beginTransaction();

        //契約情報の削除
        try {

            $where = [['id', $request->company_id]];
            $companyObj->update($where, $data);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        //その他も消しに行く（どこまで行く？）

        try {
            //加盟店アカウントテーブルの削除
            $caObj = $this->companyAccountRepository;
            $where = [['company_id', $request->company_id]];
            $caObj->update($where, $data);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        try {
            //加盟店とHPの紐付けテーブルの削除
            $achObj = $this->associatedCompanyHpRepository;
            $where = [['company_id', $request->company_id]];
            $achObj->update($where, $data);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        try {
            //関連会社テーブルの削除
            $acObj = $this->associatedCompanyRepository;
            $where = [['parent_company_id', $request->company_id]];
            $acObj->update($where, $data);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        try {
            $where = [['subsidiary_company_id', $request->company_id]];
			$acObj->update($where,$data);
		}catch(Exception $e) {
			DB::rollback();
			throw $e;
		}

		DB::commit();
        return redirect("/admin/company/");
	}

    public function group(Request $request)
    {
        $this->checkUserRules();
		if(!$request->has("company_id") || $request->company_id == "" || !is_numeric($request->company_id)) {
			throw new Exception("No Company ID. ");
			exit;
		}

		//パラメータ取得
		$params = $request->all();
		$this->view->params=$params;

		//パンクズ設定
    	$this->view->topicPath('契約管理', "index", $this->_controller);
		$pan_arr = array("id" => $request->company_id);
    	$this->view->topicPath("契約者詳細", "detail",  $this->_controller, $pan_arr);
    	$this->view->topicPath("グループ会社設定");

		//オブジェクト取得
		$row = $this->companyRepository->getDataForId($request->company_id);
		if($row == null) {
			throw new Exception("No Company Data. ");
			exit;
		}
		$this->view->company = $row;
    
		try {
			DB::beginTransaction();

		    $acObj = $this->associatedCompanyRepository;
		    if($request->has("add_company_id") && $request->add_company_id != "" || is_numeric($request->add_company_id)) {
               
			    $data = array();
			    $data['parent_company_id']     = $request->company_id;
			    $data['subsidiary_company_id'] = $request->add_company_id;
			    $acObj->create($data);
                DB::commit();
			    return redirect("/admin/company/group/?company_id=".$request->company_id);
		    }

		    $rows = $acObj->getDataForCompanyId($request->company_id);
		    $this->view->rows = $rows;

		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
        return view('admin::company.group');
	}

    public function groupDel(Request $request)
    {
        $this->checkUserRules();
        
		if(!$request->has("del_company_id") || $request->del_company_id == "" || !is_numeric($request->del_company_id)) {
			throw new Exception("No Company ID. ");
			exit;

		}else if(!$request->has("del_id") || $request->del_id == "" || !is_numeric($request->del_id)) {
			throw new Exception("No Del ID. ");
			exit;

		}else if(!$request->has("del_pearent_company_id") || $request->del_pearent_company_id == "" || !is_numeric($request->del_pearent_company_id)) {
			throw new Exception("No Del ID. ");
			exit;
		}

		try {
			DB::beginTransaction();
		    $acObj = $this->associatedCompanyRepository;
		    $data = array();
		    $data['delete_flg'] = 1;
		    $where =[["id",$request->del_id],["parent_company_id",$request->del_pearent_company_id],["subsidiary_company_id",$request->del_company_id]];
            $acObj->update($where,$data);
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		return redirect("/admin/company/group/?company_id=".$request->del_pearent_company_id);
	}

    /**
     * @ $row
     * @throws Exception
     */
    protected function _setAutoCancel(&$row)
    {
        // ATHOME_HP_DEV-2592 【管理画面】既存会員がスタンダード変更後の地図検索画面に値が入っている／NHP-3003
        if (
            ($row->cms_plan            ==  config('constants.cms_plan.CMS_PLAN_ADVANCE')) &&
            ($row->reserve_cms_plan    ==  config('constants.cms_plan.CMS_PLAN_STANDARD'))
        ) {
            $row->map_applied_start_date        = null;
            $row->map_start_date                = null;
            $row->map_contract_staff_id            = null;
            $row->map_contract_staff_department    = null;
            $row->map_contract_staff_department    = null;
            $row->map_applied_end_date            = null;
            $row->map_end_date                    = null;
            $row->map_cancel_staff_id            = null;
            $row->map_cancel_staff_name            = null;
            $row->map_cancel_staff_department    = null;
            $row->map_remarks                    = null;
        }

        // ATHOME_HP_DEV-2452 【プラン変更】契約情報予約の自動入力について
        if (
            ($row->cms_plan            ==  config('constants.cms_plan.CMS_PLAN_STANDARD')) &&
            ($row->reserve_cms_plan    ==  config('constants.cms_plan.CMS_PLAN_ADVANCE')) &&
            ($row->map_start_date        !=  null) &&
            ($row->map_end_date        ==  null)
        ) {
            $before_reserve_start_date            = strftime('%Y-%m-%d', strtotime('-1 day', strtotime($row->reserve_start_date)));
            $row->map_applied_end_date            = $row->reserve_applied_start_date;
            $row->map_end_date                    = $before_reserve_start_date;
            $row->map_cancel_staff_id            = $row->reserve_contract_staff_id;
            $row->map_cancel_staff_name            = $row->reserve_contract_staff_name;
            $row->map_cancel_staff_department    = $row->reserve_contract_staff_department;
        }
        // ATHOME_HP_DEV-3039 地図検索設定の自動入力について
        if (
            ($row->cms_plan            ==  config('constants.cms_plan.CMS_PLAN_STANDARD')) &&
            ($row->reserve_cms_plan    ==  config('constants.cms_plan.CMS_PLAN_LITE')) &&
            ($row->map_start_date        !=  null) &&
            ($row->map_end_date        ==  null)
        ) {
            $before_reserve_start_date            = strftime('%Y-%m-%d', strtotime('-1 day', strtotime($row->reserve_start_date)));
            $row->map_applied_end_date            = $row->reserve_applied_start_date;
            $row->map_end_date                    = $before_reserve_start_date;
            $row->map_cancel_staff_id            = $row->reserve_contract_staff_id;
            $row->map_cancel_staff_name            = $row->reserve_contract_staff_name;
            $row->map_cancel_staff_department    = $row->reserve_contract_staff_department;
        }

        // ↓ATHOME_HP_DEV-2446 【プラン変更】解約情報にて「利用停止申請日」「 利用停止日」「 解約担当者」が入力された場合の自動入力機能
        $ser    = $row->secondEstate()->first();
        if ($ser != null) {
            // ATHOME_HP_DEV-3039 2次広告自動公開設定の自動入力について
            if (
                ($row->cms_plan            >=  config('constants.cms_plan.CMS_PLAN_STANDARD')) &&
                ($row->reserve_cms_plan    ==  config('constants.cms_plan.CMS_PLAN_LITE'))  &&
                ($ser->start_date          !=  null)  &&
                ($ser->end_date            ==  null)
            ) {
                $before_reserve_start_date            = strftime('%Y-%m-%d', strtotime('-1 day', strtotime($row->reserve_start_date)));
                $ser->applied_end_date                = $row->reserve_applied_start_date;
                $ser->end_date                        = $before_reserve_start_date;
                $ser->cancel_staff_id                = $row->reserve_contract_staff_id;
                $ser->cancel_staff_name                = $row->reserve_contract_staff_name;
                $ser->cancel_staff_department        = $row->reserve_contract_staff_department;
            }
            if (
                ($row->cms_plan            !=  config('constants.cms_plan.CMS_PLAN_NONE')) &&
                ($ser->start_date            !=  null) &&
                ($ser->end_date            ==  null)
            ) {
                $ser->applied_end_date                = $row->applied_end_date;
                $ser->end_date                        = $row->end_date;
                $ser->cancel_staff_id                = $row->cancel_staff_id;
                $ser->cancel_staff_name                = $row->cancel_staff_name;
                $ser->cancel_staff_department        = $row->cancel_staff_department;
            }

            if (
                ($row->cms_plan            ==  config('constants.cms_plan.CMS_PLAN_STANDARD')) &&
                ($row->map_start_date        !=  null) &&
                ($row->map_end_date        ==  null)
            ) {
                $row->map_applied_end_date            = $row->applied_end_date;
                $row->map_end_date                    = $row->end_date;
                $row->map_cancel_staff_id            = $row->cancel_staff_id;
                $row->map_cancel_staff_name            = $row->cancel_staff_name;
                $row->map_cancel_staff_department    = $row->cancel_staff_department;
            }
            $ser->save();
        }

        //ATHOME_HP_DEV-3826
        Original::_setAutoCancel($row);
    }

    protected function _setPlan(&$params, &$row, $copy_from_member_no = null)
    {
        $startDate    = strtotime($params['reserve']['reserve_start_date']);
        if (($startDate !== false) && ($startDate < time())) {    // 予約の利用開始日が当日以前なら即時反映
            $hp                = $row->getCurrentHp();
            $changer            = new ChangeCms();
            $allowUpdateCheck    = !($copy_from_member_no);
            if ($hp && $allowUpdateCheck)    // デモコピーだと現在の契約情報を強制的に変更
            {
                $changer->changePlan($hp->id,    $row);
            } else {
                $changer->updatePlanInfo($row, false);
            }
        }
    }

    /**
     * 新規登録・編集完了表示
     */
    public function comp(Request $request)
    {

        $this->view->topicPath('契約管理', "index", $this->_controller);
        $this->view->topicPath("契約者登録完了");

        //パラメータ取得
        $params = $request->all();
        $this->view->params = $params;

        //オブジェクト取得
        $this->companyRepository = $this->companyRepository;
        //契約者情報の取得
        $row = $this->companyRepository->getDataForId($request->id);

        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }
        $this->view->contract_type        = $row["contract_type"];
        $this->view->reserve_cms_plan    = $row["reserve_cms_plan"];
        $this->view->cms_plan            = $row["cms_plan"];
        $company_id = $request->id;
        $isAdmin = getInstanceUser('admin')->checkIsSuperAdmin(getInstanceUser('admin')->getProfile());
        $isAgency = getInstanceUser('admin')->isAgency();

        $this->view->original_plan = false;
        if ($row->checkTopOriginal() && !$this->checkRedirectTopOriginal($row, $company_id, $isAdmin, $isAgency)) {
            $this->view->original_plan = true;
        }

        $this->view->original = $original = new Original();
        $this->view->original_setting_title = $original->getOriginalSettingTitle();
        $this->view->original_edit_title = $original->getOriginalEditTitle();
        $this->view->original_tag = $original->getEffectMeasurementTitle();

        return view('admin::company.comp');
    }

    /**
     * Check account has privilege edit toporiginal
     * return bool
     */
    public function checkRedirectTopOriginal($row, $companyId, $admin, $agency)
    {
        if (empty($row)) {
            return true;
        }
        $isPlan = !Original::checkPlanCanUseTopOriginal($row->cms_plan);
        $redirect = false;
        $canOpenAgency = getInstanceUser('admin')->getProfile()->privilege_open_flg;
        $canCreateAgency = getInstanceUser('admin')->getProfile()->privilege_create_flg;

        if (($admin && !($canOpenAgency || $canCreateAgency)) || (!$admin && !$agency)) {
            $redirect = true;
        }
        if (!$row->checkTopOriginal() || $isPlan) {
            $redirect = true;
        }
        return $redirect;
    }

    public function secondEstate(Request $request)
    {
        $this->checkUserRules();
        $row = $this->companyRepository->getDataForId($request->company_id);
        if ($row->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') && $row->reserve_cms_plan <= config('constants.cms_plan.CMS_PLAN_LITE')) {
            return redirect("/admin/company/");
        }

        $sParams = $request->session()->get('admin_second_estate');

        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $request->company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("2次広告自動公開設定");

        //設定系の情報取得
        $company_config = getConfigs('admin.company');


        //API系のURIなど
        $defailt_backbone = $company_config->backbone->api;
        $this->view->backbone = $defailt_backbone;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanySecondEstate(), 'secondEstate');
        $form->addSubForm(new CompanySecondEstateArea(), 'secondEstateArea');
        $form->addSubForm(new SecondEstateOther(), 'other');

        //パラメータ取得
        $params = $request->all();
        $this->view->params = $params;

        //登録ボタン押下時
        if ($request->has("submit-confirm") && $request->get("submit-confirm") != "") {

            //バリデーション
            $form->setData($params);
            if ($form->isValid($params)) {

                $error_flg = false;

                //利用日チェック
                if ($params['secondEstate']['applied_start_date'] != "" && $params['secondEstate']['start_date'] != "") {
                    $applied_start_date    = str_replace("-", "", $params['secondEstate']['applied_start_date']);
                    $start_date            = str_replace("-", "", $params['secondEstate']['start_date']);
                    if ($applied_start_date > $start_date) {
                        $form->getSubForm('secondEstate')->getElement('applied_start_date')->setMessages(array("利用開始申請日は、利用開始日より過去日を設定してください。"));
                        $error_flg = true;
                    }
                }

                if ($params['secondEstate']['start_date'] != "") {
                    $start_date = str_replace("-", "", $params['secondEstate']['start_date']);
                    //2次広告自動公開設定
                    $row = $this->secondEstateRepository->getDataForCompanyId($request->company_id);
                    if ($row != null) {
                        $db_start_date = substr($row['start_date'], 0, 10);
                    } else {
                        $db_start_date = "0000-00-00";
                    }
                    // 新規登録の場合のみバリデーションチェックを行う。
                    if ($db_start_date == "0000-00-00") {
                        $dt = new DateTime();
                        $dt->setTimeZone(new DateTimeZone('Asia/Tokyo'));
                        $today = $dt->format('Ymd');
                        if (strtotime($start_date) <= strtotime($today)) {
                            $form->getSubForm('secondEstate')->getElement('start_date')->setMessages(
                                array(
                                    "利用開始日が当日の場合は物件が流れ込まない為、設定できません。",
                                    "翌日以降の日付けで設定して下さい。"
                                )
                            );
                            $error_flg = true;
                        }
                    }
                }

                //利用日チェック
                if ($params['secondEstate']['applied_end_date'] != "" && $params['secondEstate']['end_date'] != "") {
                    $applied_end_date = str_replace("-", "", $params['secondEstate']['applied_end_date']);
                    $end_date = str_replace("-", "", $params['secondEstate']['end_date']);
                    if ($applied_end_date > $end_date) {
                        $form->getSubForm('secondEstate')->getElement('applied_end_date')->setMessages(array("利用停止申請日は、利用停止日より過去日を設定してください。"));
                        $error_flg = true;
                    }
                }

                //利用開始日と利用停止日のチェック
                if ($params['secondEstate']['start_date'] != "" && $params['secondEstate']['end_date'] != "") {
                    $start = str_replace("-", "", $params['secondEstate']['start_date']);
                    $end = str_replace("-", "", $params['secondEstate']['end_date']);
                    if ($start > $end) {
                        $form->getSubForm('secondEstate')->getElement('end_date')->setMessages(array("利用停止日は、利用開始日より未来日を設定してください。"));
                        $error_flg = true;
                    }
                }

                //解約担当者系の設定
                if ($params['secondEstate']['cancel_staff_id'] != "" && ($params['secondEstate']['cancel_staff_name'] == "" || $params['secondEstate']['cancel_staff_department'] == "")) {
                    $form->getSubForm('secondEstate')->getElement('cancel_staff_name')->setMessages(array("解約担当者名が設定されていません。参照ボタンより取得してください。"));
                    $error_flg = true;
                } else if ($params['secondEstate']['cancel_staff_id'] == "" && ($params['secondEstate']['cancel_staff_name'] != "" || $params['secondEstate']['cancel_staff_department'] != "")) {
                    $form->getSubForm('secondEstate')->getElement('cancel_staff_id')->setMessages(array("解約担当者が設定されていません。"));
                    $error_flg = true;
                }

                // 4304: Check start date plan future
                $rowEstate = $this->companyRepository->getDataForId($request->company_id)->toArray();
                if ($rowEstate != null) {
                    if ($rowEstate['cms_plan'] == config('constants.cms_plan.CMS_PLAN_LITE') && ($rowEstate['reserve_cms_plan'] == config('constants.cms_plan.CMS_PLAN_STANDARD') || $rowEstate['reserve_cms_plan'] == config('constants.cms_plan.CMS_PLAN_ADVANCE')) && $params['secondEstate']['start_date'] != "") {
                        $startDate = str_replace("-", "", $params['secondEstate']['start_date']);
                        $reserveStartDate = substr($rowEstate['reserve_start_date'], 0, 10);
                        $reserveStartDate = str_replace("-", "", $reserveStartDate);
                        if ($startDate < $reserveStartDate) {
                            $form->secondEstate->getElement('start_date')->setMessages(array("利用開始日はプラン契約適用日以降の日付を設定してください。"));
                            $error_flg = true;
                        }
                    }
                }

                $form->checkErrors();

                if (!$error_flg) {
                    $request->session()->put('admin_second_estate', $params);
                    return redirect('/admin/company/second-estate-confirm?company_id=' . $params["company_id"]);
                    exit;
                }
            }

            //戻るボタン押下時
        } else if ($sParams && is_array($sParams) && array_key_exists('back', $sParams)) {
            unset($sParams['back']);
            $request->session()->put('admin_second_estate', $sParams);
            $form->setData($sParams);

            //
        } else {

            //2次広告自動公開設定
            $row = $this->secondEstateRepository->getDataForCompanyId($request->company_id);

            if ($row != null) {

                //日付周りの調整
                $applied_start_date = substr($row['applied_start_date'], 0, 10);
                if ($applied_start_date == "0000-00-00") $applied_start_date = "";
                $row['applied_start_date'] = $applied_start_date;

                $start_date = substr($row['start_date'], 0, 10);
                if ($start_date == "0000-00-00") $start_date = "";
                $row['start_date'] = $start_date;

                $applied_end_date = substr($row['applied_end_date'], 0, 10);
                if ($applied_end_date == "0000-00-00") $applied_end_date = "";
                $row['applied_end_date'] = $applied_end_date;

                $end_date = substr($row['end_date'], 0,  10);
                if ($end_date == "0000-00-00") $end_date = "";
                $row['end_date'] = $end_date;
            }


            if (!is_null($row)) {
                $area_search_filter = json_decode($row->area_search_filter);
                $selectedPrefs = $area_search_filter->area_1;
                $secondEstateArea = $form->getSubForm('secondEstateArea')->getElements();

                foreach ($secondEstateArea as $name => $element) {
                    $element->setValue($selectedPrefs);
                }

                $form->setData($row->toArray());
            }
        }
        return view('admin::company.second-estate');
    }

    /**
     * ２次広告自動公開設定
     */
    public function secondEstateConfirm(Request $request)
    {
        $this->checkUserRules();

        $row = $this->companyRepository->getDataForId($request->company_id);
        if ($row->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') && $row->reserve_cms_plan <= config('constants.cms_plan.CMS_PLAN_LITE')) {
            return redirect("/admin/company/");
        }
        // セッション
        $sParams = $request->session()->get('admin_second_estate');

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $request->company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("2次広告自動公開設定");

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanySecondEstate(), 'secondEstate');
        $form->addSubForm(new CompanySecondEstateArea(), 'secondEstateArea');
        $form->addSubForm(new SecondEstateOther(), 'other');
        $form->setData($sParams);

        //パラメータ取得
        $params = $request->all();
        $this->view->params = $params;

        //登録ボタン押下時
        if ($request->has("submit-complete") && $params['submit-complete'] != "") {
            $area_search_filter = array();
            $pref = array();

            // 都道府県番号
            foreach ($sParams['secondEstateArea'] as $area) {
                foreach ($area as $value) {
                    $pref[] = $value;
                }
            }
            $area_search_filter['area_1'] = $pref;

            $area_search_filter = json_encode($area_search_filter);
            $seParams = $sParams['secondEstate'];

            $data = array();
            $data['company_id']                 = $sParams['company_id'];
            $data['applied_start_date']         = $seParams['applied_start_date'];
            $data['start_date']                 = $seParams['start_date'];
            $data['contract_staff_id']             = $seParams['contract_staff_id'];
            $data['contract_staff_name']         = $seParams['contract_staff_name'];
            $data['contract_staff_department']     = $seParams['contract_staff_department'];
            $data['applied_end_date']             = empty($seParams['applied_end_date']) ? NULL : $seParams['applied_end_date'];
            $data['end_date']                     = empty($seParams['end_date']) ? NULL : $seParams['end_date'];
            $data['cancel_staff_id']             = empty($seParams['cancel_staff_id']) ? NULL : $seParams['cancel_staff_id'];
            $data['cancel_staff_name']             = empty($seParams['cancel_staff_name']) ? NULL : $seParams['cancel_staff_name'];
            $data['cancel_staff_department']     = empty($seParams['cancel_staff_department']) ? NULL : $seParams['cancel_staff_department'];
            $data['area_search_filter']         = $area_search_filter;
            $data['remarks']                     = empty($sParams['other']['remarks']) ? "" : $sParams['other']['remarks'];

            try {
                DB::beginTransaction();


                if (is_null($seParams['id']) || empty($seParams['id'])) {

                    // すでにcompany_idが存在する場合は Exception
                    $secondEstateRow = $this->secondEstateRepository->getDataForCompanyId($data['company_id']);
                    if (!is_null($secondEstateRow)) {
                        throw new Exception();
                    }

                    $this->secondEstateRepository->create($data);
                } else {
                    $this->secondEstateRepository->update($seParams['id'], $data);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            return redirect('/admin/company/second-estate-complete?company_id=' . $params['company_id']);
            exit;

            //戻るボタン押下時
        } else if ($request->has("back") && $request->back != "") {
            $sParams['back'] = true;
            $request->session()->put('admin_second_estate', $sParams);
            return redirect("/admin/company/second-estate?company_id=" . $params['company_id']);
        }

        return view('admin::company.second-estate-confirm');
    }

    /**
     * ２次広告自動公開設定
     */
    public function secondEstateComplete(Request $request)
    {
        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $request->company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("2次広告自動公開設定");

        //パラメータ取得
        $params = $request->all();
        $this->view->params = $params;
        //契約者情報の取得
        $row = $this->companyRepository->getDataForId($request->company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        $this->view->contract_type        = $row["contract_type"];
        $this->view->reserve_cms_plan    = $row["reserve_cms_plan"];
        $this->view->cms_plan            = $row["cms_plan"];
        $company_id = $request->company_id;
        $isAdmin = Admin::getInstance()->checkIsSuperAdmin(Admin::getInstance()->getProfile());
        $isAgency = Admin::getInstance()->isAgency();

        $this->view->original_plan = false;
        if ($row->checkTopOriginal() && !$this->checkRedirectTopOriginal($row, $company_id, $isAdmin, $isAgency)) {
            $this->view->original_plan = true;
        }
        $this->view->original_setting_title = Original::getOriginalSettingTitle();
        $this->view->original_edit_title = Original::getOriginalEditTitle();
        $this->view->original_tag = Original::getEffectMeasurementTitle();

        return view('admin::company.second-estate-complete');
    }

    public function getPrivate(Request $request)
    {
        $id = $request->company_id;
        $this->checkUserRules();
        if (!$request->has("company_id") || $request->company_id == "" || !is_numeric($request->company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $row = $this->companyRepository->getDataForId($request->company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        // パラメータ取得
        $params = $request->all();
        $this->view->params = $params;

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $request->company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("非公開設定");

        //FTPしにいけるかのちぇっく
        $assoc = App::make(AssociatedCompanyHpRepositoryInterface::class);
        $assocRow = $assoc->fetchRow([['company_id', $id]]);
        $ftp_flg = true;
        //FTP情報があるか？
        if ($row['ftp_server_name'] == "" || $row['ftp_user_id'] == "" || $row['ftp_password'] == "") {
            $ftp_flg = false;
        } else if ($assocRow == null) {
            $ftp_flg = false;

            //HPを作成始めているか？
        } else if ($row->getCurrentHp() == false) {
            $ftp_flg = false;
        }
        //FTP繋いで、HTMLを消しに行く
        if ($request->has("del_flg") || $request->input("del_flg") != "") {
            DB::beginTransaction();
            //ログを残す
            $ldObj = App::make(LogDeleteRepositoryInterface::class);
            try {
                $item = array();
                $profile = getInstanceUser('admin')->getProfile();
                $item["manager_id"] = $profile->id;
                $item["hp_id"]      = $assocRow->current_hp_id;
                $item["company_id"] = $id;
                $item["datetime"]   = date("Y-m-d H:i:s");
                $ldObj->create($item);
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

            try {
                // ATHOME_HP_DEV-6186
                $isDemoSite     = ($row->contract_type == config('constants.company_agreement_type.CONTRACT_TYPE_DEMO'));
                if ($isDemoSite) {
                    $config         = getConfigs('sale_demo');
                    $demoDomain     = $config->domain;
                    $ftpPassWord    = $row->ftp_password;
                    $ftpUserName    = $row->ftp_user_id;
                    $originFtpUserName = $ftpUserName;
                    if (isset($config->ftpaccount->additional) && strlen($config->ftpaccount->additional)) {
                        $ftpUserName .= $config->ftpaccount->additional;
                    }
                    $api            = "http://api.apache.{$demoDomain}/addUser.php";
                    $api            = "{$api}?user={$ftpUserName}";
                    $api            = "{$api}&pass={$ftpPassWord}&key={$config->api->key}";
                    file_get_contents($api);
                    $row->ftp_server_name   = "ftp.{$demoDomain}";
                    $row->domain            = "{$originFtpUserName}.{$demoDomain}";
                    $row->ftp_directory     = "{$originFtpUserName}.{$demoDomain}";
                    $row->ftp_user_id       = "{$ftpUserName}";
                }
                //HTMLをガシガシ消しに行く
                $ftp = new Ftp($row->ftp_server_name);
                //ログインする
                $ftp->login($row->ftp_user_id, $row->ftp_password);

                //パッシブモードの設定

                if($row->ftp_pasv_flg == config('constants.ftp_pasv_mode.IN_FORCE'))
                  $ftp->pasv(true);

                //HTMLが置かれているディレクトリに移動
                $ftp->chdir($row->ftp_directory);

                $list = $ftp->rawlist("./");
                foreach($list as $key => $val) {

                    $child = preg_split("/\s+/", $val);

                    if($child[8] == "." || $child[8] == "..") continue;

                    if($child[0][0] === "d") {
                        $ftp->rmdir($child[8]);
                    }else{
                        $ftp->delete($child[8]);
                    }
                }

            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            try {
                // 公開済みの物件検索設定の削除
                $row->deletePublicSearch();
                // 公開済み特集の削除
                $row->deletePublicSpecial();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            DB::commit();

            $this->updateFlg($row);
            return redirect('/admin/company/private-cmp' . '?company_id=' . $request->company_id);
        }
        return view('admin::company.private')->with('company', $row)->with('ftp_flg', $ftp_flg)->with('id', $id);
    }
    private function updateFlg($companyRow)
    {   
        $table = App::make(HpPageRepositoryInterface::class);

        DB::beginTransaction();

        $hpTable = App::make(HpRepositoryInterface::class);
        $reserveTable = App::make(ReleaseScheduleRepositoryInterface::class);
        $hp = array();
        if ($row = $companyRow->getCurrentHp()) {
            $hp[] = $row;
        }
        foreach ($hp as $row) {
            // ATHOME_HP_DEV-3126
            $row->all_upload_flg = 1;
            $row->setAllUploadParts('ALL', 1);
            $row->save();
            $table->update([['hp_id', $row->id]],['public_flg' => 0, 'public_path' => NULL]);
            $reserveTable->update([['hp_id', $row->id]],['delete_flg' => 1]);
        }
        DB::commit();
    }
    public function privateCmp(Request $request)
    {
        //パラメータ取得
        $this->view->params = $request->all();
        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $request->company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("非公開設定");

        return view('admin::company.private-cmp');
    }
    private function checkUserRules()
    {
        $user = Admin::getInstance();
        if ($user->isAgency() && !$this->checkPrivilegeEdit(getInstanceUser('admin')->getProfile()->id)) {
            return redirect('/admin/company/');
        }
    }

 	private function checkPrivilegeEdit($id) {
        $dataLogin = App::make(ManagerRepositoryInterface::class)->find($id);
        if ($dataLogin->privilege_edit_flg == 1) {
            return true;
        }
        return false;
    }

    public function initializeCms(Request $request)
    {
        set_time_limit(0);
        // CMS初期化独自の権限チェック:「修正権限」「代行更新権限」が有無なら初期化権限あり
        $dataLogin = Admin::getInstance()->getProfile();
        if ($dataLogin->privilege_edit_flg != 1 && $dataLogin->privilege_open_flg != 1) {
            return redirect('/admin/company/');
            exit;
        }

        if (!$request->has("company_id") || $request->company_id == "" || !is_numeric($request->company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }
        //オブジェクト取得
        $companyRow = $this->companyRepository->getDataForId($request->company_id);
        if ($companyRow == null) {
            // throw new Exception("No Company Data. ");
            exit;
        }
        if (!in_array(
            $companyRow->contract_type,
            [config('constants.company_agreement_type.CONTRACT_TYPE_PRIME'), config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')],
            true
        )) {
            throw new Exception("本契約・デモ契約の会員のみが対象です。");
            exit;
        }
        $this->view->company = $companyRow;
        //パラメータ取得
        $params = $request->all();
        $this->view->params = $params;

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $request->company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("CMSデータ削除");

        $assoc = App::make(AssociatedCompanyHpRepositoryInterface::class);
        $assocRow = $companyRow->associatedCompanyHp()->first();
        if (is_null($assocRow) || $assocRow->current_hp_id == 0) {
            $this->view->nocms = 1;
        } else {
            $this->view->nocms = 0;
        }
        if ($request->has("del_flg") || $request->input("del_flg") != "") {
            // CSRF チェック
			if(is_null($request->_token)) {
				throw new Exception("No Posted CSRF-Token");
            }
            DB::beginTransaction();
            try {
                // DB削除も実施する場合はこっち
                if (self::INITIALIZE_DB_ROWS) {
                    // 会員コンテンツ削除
                    if (!is_null($assocRow->current_hp_id) && $assocRow->current_hp_id > 0) {
                        $hp = $companyRow->getCurrentHp();
                        $hp->deleteAll(true);
                    }
                    // 代行制作コンテンツ削除
                    if (!is_null($assocRow->space_hp_id) && $assocRow->space_hp_id > 0) {
                        $space_hp = $companyRow->getCurrentCreatorHp();
                        $space_hp->deleteAll(true);
                    }
                    // バックアップコンテンツ削除
                    if (!is_null($assocRow->backup_hp_id) && $assocRow->backup_hp_id > 0) {
                        $backup_hp = $companyRow->getBackupHp();
                        $backup_hp->deleteAll(true);
                    }
                } 
                // コンテンツ削除
                switch ($companyRow->contract_type) {
                    case config('constants.company_agreement_type.CONTRACT_TYPE_PRIME'):  // 本契約

                        // テストサイトを削除する
                        if (!is_null($assocRow->current_hp_id) && $assocRow->current_hp_id > 0) {
                            $publishType = 2;
                            $this->_delUploadedContent($assocRow->current_hp_id, $publishType);
                        }
                        // 代行テストサイトの削除
                        if (!is_null($assocRow->space_hp_id) && $assocRow->space_hp_id > 0) {
                            $publishType = 3;
                            $this->_delUploadedContent($assocRow->space_hp_id, $publishType);
                        }
                        break;
                    case config('constants.company_agreement_type.CONTRACT_TYPE_DEMO'): // デモ契約
                        // 本番&テストサイトを削除する
                        if (!is_null($assocRow->current_hp_id) && $assocRow->current_hp_id > 0) {
                            $publishType = 1;
                            $this->_delUploadedContent($assocRow->current_hp_id, $publishType);
                            $publishType = 2;
                            $this->_delUploadedContent($assocRow->current_hp_id, $publishType);
                        }

                        // 代行テストサイトの削除
                        if (!is_null($assocRow->space_hp_id) && $assocRow->space_hp_id > 0) {
                            $publishType = 3;
                            $this->_delUploadedContent($assocRow->space_hp_id, $publishType);
                        }
                        break;
                }

                $init_hp_id_list = [];

                // current_hp_id -> 0
                $assoc->updateCurrentHp($request->company_id, 0);
                $init_hp_id_list['current_hp_id'] = sprintf("%d - 0", $assocRow->current_hp_id);

                // space_hp_id -> null
                if (!is_null($assocRow->space_hp_id)) {
                    $assoc->updateCreatorHp($request->company_id, null);
                    $init_hp_id_list['space_hp_id'] = sprintf("%d - null", $assocRow->space_hp_id);
                }

                // backup_hp_id -> null
                if (!is_null($assocRow->backup_hp_id)) {
                    $init_hp_id_list['backup_up_id'] = sprintf("%d - null", $assocRow->backup_hp_id);
                    $assoc->updateBackupHp($request->company_id, null);
                }
                $item = array();
                $profile = Admin::getInstance()->getProfile();
                $item["manager_id"] = $profile->id;
                $item["company_id"] = $request->company_id;
                $item["datetime"]   = date("Y-m-d H:i:s");
                $item["hp_id_list"] = json_encode($init_hp_id_list, true);
                $this->logInitializeCmsRepository->create($item);
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            
            DB::commit();
            echo '<script type="text/javascript">parent.location.href="/admin/company/initialize-cms-cmp/company_id/' . $request->company_id . '";</script>';
            exit;
        } else {
            getUser()->regenerateCsrfToken(); 
            return view('admin::company.initialize-cms');
        }
    }
    public function initializeCmsCmp($id)
    {       
        //パラメータ取得
        $params['company_id'] = $id;
        $this->view->params = $params;

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("CMSデータ削除");

        return view('admin::company.initialize-cms-cmp');
    }
    private function _delUploadedContent($hp_id, $publishType)
    {
        $ftp = new \Library\Custom\Publish\Ftp($hp_id, $publishType);
        $ftp->login();
        $domain = $ftp->getCompany()->domain;
        $mode = $ftp->getPublishName($publishType);
        $delDirs = [];
        $delDirs[] = 'files/' . $mode;          // filesは必須

        if($mode == 'public') {
            $delDirs[] = $domain;
            $delDirs[] = "www." . $domain;
        } else {
            $delDirs[] = $mode . "." . $domain;
        }
        foreach($delDirs as $delDir) {
            echo str_pad(" ", ini_get('output_buffering'), ' ', STR_PAD_RIGHT) . "\n";  // polling
            $ftp->allRemove($delDir, false);
        }
        $ftp->close();
    }

    /**
     * Top original setting
     */
    public function originalSetting(Request $request)
    {
        $this->checkUserRules();
        
        $sParams = $request->session()->get('admin-original-setting');
        $company_id = $request->company_id;
        $controller = $this->_controller;
        $row = $this->companyRepository->getDataForId($company_id);

        if(!Original::checkPlanCanUseTopOriginal($row->cms_plan) && !Original::checkPlanCanUseTopOriginal($row->reserve_cms_plan)){
            return redirect('/admin/company/detail?id='.$company_id);
        }

        $this->view->topicPath('契約管理', "index", $controller);
        $this->view->topicPath("契約者詳細", "detail", $controller, array("id" => $company_id));
        $this->view->topicPath(Original::getOriginalSettingTitle());

        $company_config = getConfigs('admin.company');

        $defailt_backbone = $company_config->backbone->api;
        $this->view->backbone = $defailt_backbone;
        $this->view->original_title = Original::getOriginalSettingTitle();
        $this->view->contract_title = config('constants.original.CONTRACT_TITLE');

        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanySecondEstate(),'originalSetting');
        $form->addSubForm(new SecondEstateOther(),  'other');

        $params = $request->all();
        $this->view->params = $params;

        if ($request->has("submit-confirm") && $request->get("submit-confirm") != "") {
            $form->setData($params);
            if ($form->isValid($params)) {
                $error_flg = false;
                if ($params['originalSetting']['applied_start_date'] != "" && $params['originalSetting']['start_date'] != "") {
                    $applied_start_date = str_replace("-", "", $params['originalSetting']['applied_start_date']);
                    $start_date = str_replace("-", "", $params['originalSetting']['start_date']);
                    if ($applied_start_date > $start_date) {
                        $form->getSubForm('originalSetting')->getElement('applied_start_date')->setMessages( array("利用開始申請日は、利用開始日より過去日を設定してください。"));
                        $error_flg = true;
                    }
                }

                if ($params['originalSetting']['applied_end_date'] != "" && $params['originalSetting']['end_date'] != "") {
                    $applied_end_date = str_replace("-", "", $params['originalSetting']['applied_end_date']);
                    $end_date = str_replace("-", "", $params['originalSetting']['end_date']);
                    if ($applied_end_date > $end_date) {
                        $form->getSubForm('originalSetting')->getElement('applied_end_date')->setMessages( array("利用停止申請日は、利用停止日より過去日を設定してください。"));
                        $error_flg = true;
                    }
                }

                if ($params['originalSetting']['start_date'] != "" && $params['originalSetting']['end_date'] != "") {
                    $start = str_replace("-", "", $params['originalSetting']['start_date']);
                    $end = str_replace("-", "", $params['originalSetting']['end_date']);
                    if ($start > $end) {
                        $form->getSubForm('originalSetting')->getElement('end_date')->setMessages( array("利用停止日は、利用開始日より未来日を設定してください。"));
                        $error_flg = true;
                    }
                }

                if ($params['originalSetting']['cancel_staff_id'] != "" && ($params['originalSetting']['cancel_staff_name'] == "" || $params['originalSetting']['cancel_staff_department'] == "")) {
                    $form->getSubForm('originalSetting')->getElement('cancel_staff_name')->setMessages( array("解約担当者名が設定されていません。参照ボタンより取得してください。"));
                    $error_flg = true;
                } else if($params['originalSetting']['cancel_staff_id'] == "" && ($params['originalSetting']['cancel_staff_name'] != "" || $params['originalSetting']['cancel_staff_department'] != "")) {
                    $form->getSubForm('originalSetting')->getElement('cancel_staff_id')->setMessages( array("解約担当者が設定されていません。") );
                    $error_flg = true;
                }

                // 4304: Check start date plan future
                if ($row != null) {
                    if (($row['cms_plan'] == config('constants.cms_plan.CMS_PLAN_LITE') || $row['cms_plan'] == config('constants.cms_plan.CMS_PLAN_STANDARD')) && $row['reserve_cms_plan'] == config('constants.cms_plan.CMS_PLAN_ADVANCE') && $params['originalSetting']['start_date'] != "") {
                        $startDate = str_replace("-", "", $params['originalSetting']['start_date']);
                        $reserveStartDate = substr($row['reserve_start_date'], 0, 10);
                        $reserveStartDate = str_replace("-", "", $reserveStartDate);
                        if ($startDate < $reserveStartDate) {
                            $form->getSubForm('originalSetting')->getElement('start_date')->setMessages(array("利用開始日はプラン契約適用日以降の日付を設定してください。"));
                            $error_flg = true ;
                        }
                    }
                }

                $form->checkErrors();

                if (!$error_flg) {
                    $request->session()->put('admin-original-setting', $params);
                    return redirect('/admin/company/original-setting-confirm?company_id='.$params["company_id"]);
                }
            }
        }else if( is_array($sParams) && array_key_exists('back', $sParams) ){
            unset($sParams['back']);
            $request->session()->put('admin-original-setting', $sParams);
            $form->setData($sParams);
        }else{
            $row = $this->originalSettingRepository->getDataForCompanyId($request->company_id);
            if ($row != null) {
                $applied_start_date = substr($row['applied_start_date'], 0, 10);
                if($applied_start_date == "0000-00-00") $applied_start_date = "";
                $row['applied_start_date'] = $applied_start_date;

                $start_date = substr($row['start_date'], 0, 10);
                if($start_date == "0000-00-00") $start_date = "";
                $row['start_date'] = $start_date;

                $applied_end_date = substr($row['applied_end_date'], 0, 10);
                if($applied_end_date == "0000-00-00") $applied_end_date = "";
                $row['applied_end_date'] = $applied_end_date;

                $end_date = substr($row['end_date'],0,  10);
                if($end_date == "0000-00-00") $end_date = "";
                $row['end_date'] = $end_date;
            }

            if (!is_null($row)){
                $form->setData($row->toArray());
            }
        }
        return view('admin::company.original-setting');
    }

    /**
     * Top original setting confirm
     */
    public function originalSettingConfirm(Request $request)
    {
        $this->checkUserRules();
        
        $sParams = $request->session()->get('admin-original-setting');
        $company_id = $request->company_id;

        if(!is_numeric($company_id)){
            return redirect('/admin/company');
        }
        $controller = $this->_controller;
        $row = $this->companyRepository->getDataForId($company_id);

        // 4304: Check start date plan future
        if(!$row || (!Original::checkPlanCanUseTopOriginal($row->cms_plan) && !Original::checkPlanCanUseTopOriginal($row->reserve_cms_plan))){
            return redirect('/admin/company/detail/?id='.$company_id);
        }

        $this->view->topicPath('契約管理', "index", $controller);
        $this->view->topicPath("契約者詳細", "detail", $controller, array("id" => $company_id));
        $this->view->topicPath(Original::getOriginalSettingTitle());

        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanySecondEstate(),'originalSetting');
        $form->addSubForm(new SecondEstateOther(),  'other');
        $form->setData($sParams);

        $params = $request->all();
        $this->view->params = $params;
        $this->view->original_title = Original::getOriginalSettingTitle();
        $this->view->original_sub_title = Original::getOriginalSettingSubTitle();

        if ($request->has("submit-complete") && $request->get("submit-complete") != "") {
            $seParams = $sParams['originalSetting'];
            $data = array();
            $data['company_id'] = $sParams['company_id'];
            $data['applied_start_date'] = $seParams['applied_start_date'];
            $data['start_date'] = $seParams['start_date'];
            $data['contract_staff_id'] = $seParams['contract_staff_id'];
            $data['contract_staff_name'] = $seParams['contract_staff_name'];
            $data['contract_staff_department'] = $seParams['contract_staff_department'];
            $data['applied_end_date'] = empty($seParams['applied_end_date']) ? null : $seParams['applied_end_date'];
            $data['end_date'] = empty($seParams['end_date']) ? null : $seParams['end_date'];
            $data['cancel_staff_id'] = empty($seParams['cancel_staff_id']) ? null : $seParams['cancel_staff_id'];
            $data['cancel_staff_name'] = empty($seParams['cancel_staff_name']) ? null : $seParams['cancel_staff_name'];
            $data['cancel_staff_department'] = empty($seParams['cancel_staff_department']) ? null : $seParams['cancel_staff_department'];
            $data['remarks'] = empty($sParams['other']['remarks']) ? "" : $sParams['other']['remarks'];

            DB::beginTransaction();
            try {
                $topBefore = $row->checkTopOriginal();
                if (is_null($seParams['id']) || empty($seParams['id'])){
                    $this->originalSettingRepository->create($data);
                } else{
                    // $where = array("id = ?" => $seParams['id']);
                    $this->originalSettingRepository->update($seParams['id'], $data);
                }

                $originalSettingData = $this->originalSettingRepository->getDataForCompanyId($row->id);

                $checkStartDate = Original::checkDate($originalSettingData->start_date);
                $checkEndDate = Original::checkDate($originalSettingData->end_date);

                switch ($checkStartDate){
                    // start date < today, if is top => exe now
                    // ATHOME_HP_DEV-4448: change spec start date <= today
                    case config('constants.original.CURRENT_DATE'):
                    case config('constants.original.PAST_DATE'):
                        $topTo = true;
                        // expired?? Remove top.
                        if($checkEndDate == config('constants.original.PAST_DATE')) {
                            $topTo = false;
                        }
                        break;
                    default:
                        $topTo = false;
                }
                Original::callTopOriginalEvent($row, $topTo, $topBefore);
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

            return redirect('/admin/company/detail?id='.$params['company_id']);

        } else if($request->has("back") && $request->back != "") {
            $sParams['back'] = true;
            $request->session()->put('admin-original-setting', $sParams);
            return redirect("/admin/company/original-setting?company_id=".$params['company_id']);
        }
        return view('admin::company.original-setting-confirm');
    }

    public function originalEdit(Request $request)
    {
        $company_id = $request->company_id;
        $controller = $this->_controller;
        $row = $this->companyRepository->getDataForId($company_id);
        $isAdmin = getInstanceUser('admin')->checkIsSuperAdmin(getInstanceUser('admin')->getProfile());
        $isAgency = getInstanceUser('admin')->isAgency();

        if ($this->checkRedirectTopOriginal($row, $company_id, $isAdmin, $isAgency)) {
            return redirect('/admin/company/detail?id='.$company_id);
        }

        $this->view->topicPath('契約管理', "index", $controller);
        $this->view->topicPath("契約者詳細", "detail", $controller, array("id" => $company_id));
        $this->view->topicPath(Original::getOriginalEditTitle());
        $this->view->original_edit_title = Original::getOriginalEditTitle();
        $this->view->original_edit_sub_title = Original::getOriginalEditSubTitle();
        $params = $request->all();
        $this->view->params = $params;

        $original = new Original();
        $this->view->original_link = $original->getOriginalName($company_id);
        return view('admin::company.original-edit');
    }

    /**
     * 06_契約管理:グロナビ設定／オリジナルタグ編集・一覧
     * Setting global navigation/Edit original tag-list
     * @throws Exception
     */
    public function navigationTagList(Request $request){
        $companyId = $request->company_id;
        $row = $this->_checkCompanyTOP($companyId);

        $screenId = config('constants.original.ORIGINAL_EDIT_NAVIGATION');
        $detailScreenId = config('constants.original.ORIGINAL_EDIT_CMS');
        $specialScreenId = config('constants.original.ORIGINAL_EDIT_SPECIAL');
        //パンクズ設定 - Breadcrumb
        $original = new Original();
        $this->breadcrumbTOPEdit($original->getScreenTitle($screenId),$companyId);

        $isAdmin = getInstanceUser('admin')->checkIsSuperAdmin(getInstanceUser('admin')->getProfile());
        $isAgency = getInstanceUser('admin')->isAgency();

        if ($this->checkRedirectTopOriginal($row, $companyId, $isAdmin, $isAgency)) {
            return redirect('/admin/company/detail?id='.$companyId);
        }

        // get params;
        $params = $request->all();

        $params['backTOP'] = $original->getScreenUrl($detailScreenId,$companyId);
        $params['redirectEditHousingBlock'] = $original->getScreenUrl($specialScreenId,$companyId);
        $params['currentUrl'] = $original->getScreenUrl($screenId,$companyId);

        // get current HP
        /** @var App\Models\Hp $hp */
        $hp = $row->getCurrentCreatorHp();

        if(!$hp){
            $this->view->hp = null;
            $this->view->params = $params;
            return view('admin::company.navigation-tag-list');
        }

        $params['max_global_navigation'] = config('constants.original.MAX_GLOBAL_NAVIGATION');

        //create forms
        $formNavigation = new TopGlobalNavigation();
        $this->view->form = $form = new Form();
        $form->addSubForm( $formNavigation, 'navigation');
        $formNavigation->setData([
            'global_navigation' => $hp->global_navigation
        ]);

        // NHP-3751:フリーワード検索タグ
        $formFreewordSetting = new TopFreewordSetting();
        $form->addSubForm($formFreewordSetting, 'freeword_setting');

        $form->setData($params);

        // submit navigation
        if ($request->isXmlHttpRequest() && $request->isMethod('post')) {
            if (isset($params['navigation'])) {
                if ($formNavigation->isValid($params['navigation'])) {
                    $nav = $params['navigation']['global_navigation'];
                    $hp->global_navigation = $nav;
                    $hp->save();
                    // $this->_responseJSON(200, $this->text->get('global_navigation.success'));
                    $data = array(
                        'message'=> $this->text->get('global_navigation.success'),
                    );
                    return $this->success($data);
                }
            } else if (isset($params['freeword_setting'])) {
                if ($formFreewordSetting->isValid($params['freeword_setting'])) {
                    $original_search = [];
                    
                    for($tno=0; $tno < 5; $tno++) {
                        $rec = [
                            'type_no' => $params['freeword_setting']['type_no'][$tno],
                            'display_flg' => $params['freeword_setting']['display_flg'][$tno],
                            'display_name' => $params['freeword_setting']['display_name'][$tno],
                            'place_holder' => $params['freeword_setting']['place_holder'][$tno],
                        ];
                        $original_search[] = $rec;
                    }
                    $hp->original_search = json_encode($original_search);
                    $hp->save();
                    // $this->_responseJSON(200, $this->text->get('global_navigation.success'));
                    $data = array(
                        'message'=> $this->text->get('global_navigation.success'),
                    );
                    return $this->success($data);
                }
            }

            // $this->_responseJSON(400, $this->text->get('error'));
            return $this->error($this->text->get('error'));
        }

        // get Global Navigation
        $globalNav = $hp->getGlobalNavigation();
        $gNavArr = array();
        if($globalNav){
            $estateSetting = $hp->getEstateSetting();
            $gNavArr = $globalNav->toArray();
            $realTitle = false;
            if(isset($params['display']) && $params['display'] == '1'){
                // read real name menu agency cms
                $realTitle = true;
            }
            foreach($gNavArr as $k => &$v){
                $v['title'] = Original::getPageTitle($v,$estateSetting,$realTitle);
            }
        }
        $this->view->gNav = $gNavArr ;

        $tagsOriginal = new TagOriginal();

        $this->view->tags = array(
            'tag_site'      => $tagsOriginal->getValueTagsWithChunk(3,$tagsOriginal::CATEGORY_TAG_SITE),
            'tag_property'  => $tagsOriginal->getValueTagsWithChunk(3,$tagsOriginal::CATEGORY_TAG_PROPERTY),
            'tag_news'      => $tagsOriginal->getValueTagsWithChunk(3,$tagsOriginal::CATEGORY_TAG_NEWS) ,
            'tag_component' => $tagsOriginal->getValueTagsWithChunk(3,$tagsOriginal::CATEGORY_TAG_COMPONENT),
            'tag_element'   => $tagsOriginal->getValueTags($tagsOriginal::CATEGORY_TAG_ELEMENT),

            // NHP-3751:フリーワード検索タグ
            'tag_freeword_parts'  => $tagsOriginal->getValueTagsWithChunk(3,$tagsOriginal::CATEGORY_TAG_FREEWORD_PARTS),
        );

        // fixed tags
        $this->view->tags_nav = (object)[
            'glonavi_url' => $tagsOriginal::GLONAVI_URL,
            'glonavi_label' => $tagsOriginal::GLONAVI_LABEL,
        ];

        // global navigation sp tag
        $this->view->tags_spglonavi = $tagsOriginal->getSpGloNavi();

        $di = $this->getCurrentDirectory($request);
        $di->load(Original::getOriginalImportPath($companyId));

        // NHP-3751:フリーワード検索タグ
        // hp.original_search を取得し、json_decode => fw_type_list
        $hpOriginalSearch = $hp->original_search;

        if(empty($hpOriginalSearch)) {
            $hpOriginalSearch = null;
        }
        $freeword_type_list = json_decode($hpOriginalSearch, true); 
        $this->view->freeword_type_list = $freeword_type_list;

        $this->view->di = $di;
        $this->view->hp = $hp;
        $this->view->params = $params;
        return view('admin::company.navigation-tag-list');
    }

    public function dlTopSearchJs() {
        $file = storage_path().'/data/utility/admin/js/top-search.js';
        header("Content-Length: " . filesize ( $file ) );
        header('Content-Type: application/force-download');
        header("Content-Disposition: filename=top-search.js");
        readfile($file);
        exit;
    }

    /**
     * 07_Housing Block | Special Estate | Koma
     * 07_契約管理:物件特集コマ編集
     * @throws Exception
     */
    public function topHousingBlock(Request $request){
        $companyId = $request->company_id;
        $row = $this->_checkCompanyTOP($companyId);
        $original = new Original();
        //パンクズ設定 - Breadcrumb
        $this->breadcrumbTOPEdit(
            $original->getScreenTitle(config('constants.original.ORIGINAL_EDIT_SPECIAL')),
            $companyId
        );
        $isAdmin = getInstanceUser('admin')->checkIsSuperAdmin(getInstanceUser('admin')->getProfile());
        $isAgency = getInstanceUser('admin')->isAgency();

        if ($this->checkRedirectTopOriginal($row, $companyId, $isAdmin, $isAgency)) {
            return redirect('/admin/company/detail?id='.$companyId);
        }

        $params = $request->all();

        $params['companyId'] = $companyId;
        $params['currentUrl'] = $original->getScreenUrl(config('constants.original.ORIGINAL_EDIT_SPECIAL'),$companyId);
        $params['backTOP'] = $original->getScreenUrl(config('constants.original.ORIGINAL_EDIT_CMS'),$companyId);

        $this->view->links = array(
            'html' => $original->getOriginalImportUrl($companyId,config('constants.original.ORIGINAL_IMPORT_TOPKOMA')),
            'image' => $original->getOriginalImportUrl($companyId,config('constants.original.ORIGINAL_IMPORT_TOPIMAGE')),
            'css' => $original->getOriginalImportUrl($companyId,config('constants.original.ORIGINAL_IMPORT_TOPCSS')),
            'js' => $original->getOriginalImportUrl($companyId,config('constants.original.ORIGINAL_IMPORT_TOPJS')),
        );

        $this->view->form = new TopHousingBlock();

        // get current HP
        /** @var App\Models\Hp $hp */
        $hp = $row->getCurrentCreatorHp();

        if(!$hp){
            $this->view->hp = null;
            $this->view->settings = null;
            $this->view->params = $params;
            return view('admin::company.top-housing-block');
        }
        $settings = $hp->getEstateSetting();
        if(!$settings){
            $this->view->hp = $hp;
            $this->view->settings = null;
            $this->view->params = $params;
            return view('admin::company.top-housing-block');
        }
        $komaKey = config('constants.estate_koma.SPECIAL_ID_ATTR');
        // get HomePage
        $hpPageRepository = App::make(HpPageRepositoryInterface::class);
        $topPage = $hpPageRepository->getTopPageData($hp->id);
        // get all koma parts in homepage
        // $komaParts = $topPage->fetchPartsWithOrder(config('constants.estate_koma.PARTS_ESTATE_KOMA'),"ABS($komaKey)");
        $komaParts = $topPage->fetchPartsWithOrder(HpMainPartsRepository::PARTS_ESTATE_KOMA,"$komaKey");

        $dataKoma = array('hp' => $hp, 'page'=> $topPage, 'isTopOriginal' => true);
        $komaClass = new EstateKoma($dataKoma);

        $komaClass->disableDefault(array(
            'parts_type_code',
            'sort',
            'column_sort',
            'display_flg'
        ));

        $this->view->form = $this->_generateSpecialForm($komaParts,$settings,$komaClass,$dataKoma) ;

        $this->view->hp = $hp;
        $this->view->settings = $settings;

        if($request->isMethod('post')) {
            if($request->isXmlHttpRequest()) {
                if (isset($params['parts'])) {

                    /**
                     * @var App\Models\SpecialEstate $value
                     */
                    DB::beginTransaction();
                    try {
                        // $adapter->beginTransaction();
                        foreach ($komaParts as $k => $value) {
                            if(!isset($params['parts'][$value->id])) continue;
                            $dataSetting = $params['parts'][$value->id];
                            // $komaFormCheck = clone $komaClass;
                            $komaFormCheck = new $komaClass($dataKoma);
                            $komaFormCheck->setData($value->toArray());
                            if ($komaFormCheck->isValid($dataSetting)) {
                                $dataSetting['id'] = $value->id;
                                $komaFormCheck->setDefaults($dataSetting);
                                $komaFormCheck->setData($dataSetting);
                                $komaFormCheck->save($hp,$topPage,$value->area_id);
                            } else {
                                // $adapter->rollBack();
                                DB::rollback();
                                return $this->error($this->text->get('special_estate.setting.error'), $komaFormCheck->getMessages());
                                // $this->_responseJSON(400, $this->text->get('special_estate.setting.error'), array(
                                //     'errors' => $komaFormCheck->getMessages()
                                // ));
                            }
                            unset($komaFormCheck);
                            unset($dataSetting);
                        }
                        // $adapter->commit();
                        DB::commit();
                        return $this->success(['message' =>$this->text->get('special_estate.setting.success')]);
                        // $this->_responseJSON(200, $this->text->get('special_estate.setting.success'));
                    } catch (\Exception $e) {
                        // $adapter->rollBack();
                        DB::rollback();
                        // $this->_responseJSON(400, $this->text->get('special_estate.setting.error'));
                        return $this->error($this->text->get('special_estate.setting.error'));
                    }
                }
                // $this->_responseJSON(400, $this->text->get('special_estate.setting.error'));
                return $this->error($this->text->get('special_estate.setting.error'));
            }
        }
        
        $this->view->params = $params;
        return view('admin::company.top-housing-block');
    }

    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function apiReadTopHousingBlock(Request $request){

        /** @var Library\Custom\View\Helper\TopOriginalLang $lang */
        $lang =  $this->text;
        $params = $request->all();
        $companyId = $params['company_id'];

        /** @var App\Models\Company $company */
        $company = $this->companyRepository->getDataForId($companyId);

        if(!$company){
            // return $this->_responseJSON(400, $lang->get('error'));
            return $this->error($lang->get('error'));
        }

        DB::beginTransaction();

        try {
            // $adapter->beginTransaction();

            Original::readSpecial($company);

            // $adapter->commit();
            DB::commit();
            return $this->success(['message' => $lang->get('special_estate.setting.read_specials.success')]);
            // $this->_responseJSON(200, $lang->get('special_estate.setting.read_specials.success'));

        } catch (\Exception $e) {
            // $adapter->rollBack();
            DB::rollback();
            return $this->error($lang->get('error'));
            // $this->_responseJSON(400, $lang->get('error'));
        }

    }

    /**
     * @param Zend_Db_Table_Rowset_Abstract $komaParts
     * @param App\Models\HpEstateSetting $settings
     * @param Library\Custom\Hp\Page\Parts\EstateKoma $komaClass
     * @return Modules\Admin\Http\Form\TopHousingBlock
     */
    protected function _generateSpecialForm($komaParts, $settings, $komaClass,$dataKoma){

        $komaId = $komaClass::SPECIAL_ID_ATTR ;

        $ids = array_map(function ($ar) use ($komaId) {
            return $ar[$komaId];
        }, $komaParts->toArray());

        $specials = $settings->getSpecialAllWithPubStatus();

        $form = new TopHousingBlock();


        foreach($komaParts as $k => $part){
            /**
             * @var $part App\Models\SpecialEstateset
             */
            $partClass = new $komaClass($dataKoma);
            $form->addSubForm($partClass,"parts[$part->id]");
            $partClass->setData($part->toArray());

            /**
             *@var $currentSpecial App\Models\SpecialEstate
             */
            $currentSpecial = null;

            foreach($specials as $special){
                if($part->$komaId == $special->origin_id){
                    $currentSpecial = $special;
                    break;
                }
            }

            if($currentSpecial){

                $currentSpecialData = $currentSpecial->toArray();
                $detailForm = new TopHousingBlock();
                $detailForm->setElementsBelongsTo(null, null);
                $partClass->addSubForm($detailForm,"parts[$part->id][detail]");

                $currentSpecialData['alias'] = 'special_'.$currentSpecial->origin_id;

                $currentSpecialData['publish_status'] = ($currentSpecial->is_public)
                    ? $this->text->get('special_estate.publish_status.public')
                    : $this->text->get('special_estate.publish_status.not_public') ;
                // $types = TypeList::getInstance()->get($currentSpecialData['enabled_estate_type']);
                $types = $currentSpecial->toSettingObject()->getDisplayEstateType();
                $currentSpecialData['type'] = implode(' - ', array_map("trim",array_filter((array)$types)));

                $detailForm->setData($currentSpecialData);
            }

        }

        return $form;
    }


    /**
     * 08_Notifications
     * 08_契約管理:お知らせ設定
     * @throws Exception
     */
    public function topNotification(Request $request){

        $companyId = $request->company_id;
        $row = $this->_checkCompanyTOP($companyId);
        $original = new Original();
        $screenId = config('constants.original.ORIGINAL_EDIT_NOTIFICATION');
        $detailScreenId = config('constants.original.ORIGINAL_EDIT_CMS');

        //パンクズ設定 - Breadcrumb
        $this->breadcrumbTOPEdit($original->getScreenTitle($screenId),$companyId);

        $params = $request->all();
        $params['backTOP'] = $original->getScreenUrl($detailScreenId,$companyId);

        $isAdmin = getInstanceUser('admin')->checkIsSuperAdmin(getInstanceUser('admin')->getProfile());
        $isAgency = getInstanceUser('admin')->isAgency();

        if ($this->checkRedirectTopOriginal($row, $companyId, $isAdmin, $isAgency)) {
            return redirect('/admin/company/detail?id='.$companyId);
        }

        // get current HP
        $hp = $row->getCurrentCreatorHp();

        if(!$hp){
            $this->view->newsArr = null;
            $this->view->hp = null;
            $this->view->params = $params;
             $this->view->form = null;
            return view('admin::company.top-notification');
        }
        
        $hpPageRepository = App::make(HpPageRepositoryInterface::class);
        $topPage = $hpPageRepository->getTopPageData($hp->id);

        $notificationSettingForm = new TopNotificationSetting(array(
            'hp' => $hp,
            'page' => $topPage,
            'company' => $row,
            'isTopOriginal' => true
        ));

        $mainPartObj = new HpMainPart;

        $settings = $mainPartObj->getAllNotificationSettings($topPage->id);

        $pages = $hp->findPagesByType(HpPageRepository::TYPE_INFO_INDEX, false);

        $form = new Form();

        $notificationForm = new TopNotificationForm(array(
            'settings' => $settings,
            'hp' => $hp
        ));

        foreach(array('create','edit','delete') as $value){
            // $apiForm = clone $notificationForm;
            $apiForm = new TopNotificationForm(array(
                'settings' => $settings,
                'hp' => $hp
            ));
            $form->addSubForm($apiForm,$value);
        }

        $this->view->newsArr = $notificationForm->getParents();

        $form->addSubForm(new Form(),'page_settings');

        if(count($pages) > 0){
            $pagesForm = new Form();
            $form->addSubForm($pagesForm,'pages');
            /**
             * @var  $k
             * @var App\Repositories\HpMainParts\HpMainPartsRepository $setting
             */
            foreach($settings as $k=>$setting){

                $idField = Original::$EXTEND_INFO_LIST['page_id'];

                $details = array();
                /**
                 * @var  $k
                 * @var App\Models\HpPage $page
                 */
                foreach($pages as $page){
                    if($page->link_id != $setting->$idField) continue;

                    // $settingForm = clone $notificationSettingForm;
                    $settingForm = new TopNotificationSetting(array(
                        'hp' => $hp,
                        'page' => $topPage,
                        'company' => $row,
                        'isTopOriginal' => true
                    ));
                    $pagesForm->setElementsBelongsTo(null, null);
                    $pagesForm->addSubForm($settingForm,"settings[$k]");
                    $settingForm->setData($setting->toArray());
                    $details = $page->fetchNewsCategories();
                    break;
                }

                if(!isset($settingForm) || !$settingForm) continue;

                if(count($details)<1){
                    continue;
                }

                foreach($details as $key => $detail ){
                    // $detailForm = clone $notificationForm;
                    $detailForm = new TopNotificationForm(array(
                        'settings' => $settings,
                        'hp' => $hp
                    ));
                    $settingForm->setElementsBelongsTo(null, null);
                    $settingForm->addSubForm($detailForm,"details[$key]");
                    $detailForm->setData($detail->toArray());
                }
            }
        }
        $this->view->form = $form;
        if($request->isMethod('post') && $request->isXmlHttpRequest()){
            if(isset($params['page_settings']) && isset($params['settings'])){

                DB::beginTransaction();

                try {
                    // $adapter->beginTransaction();

                    // save settings for info index
                    foreach($params['settings'] as $k => $paramPage){
                        $checkSettingForm = new TopNotificationSetting(array(
                                'hp' => $hp,
                                'page' => $topPage,
                                'company' => $row,
                                'isTopOriginal' => true
                            ));
                        $paramPage['cms_disable'] = $paramPage['cms_disable'][count($paramPage['cms_disable'])-1];
                        foreach($settings as $k => $setting){
                            if ($setting->id == $paramPage['id']) {
                                $paramPage['sort'] = $setting->id;
                                $paramPage['column_sort'] = $setting->column_sort;
                                $paramPage['parts_type_code'] = $setting->parts_type_code;
                            }
                        }
                        $checkSettingForm->setData($paramPage);
                        if(!$checkSettingForm->isValid($paramPage)){
                            DB::rollback();
                            return $this->success(['errors', $checkSettingForm->getMessages()]);
                        }
                        $checkSettingForm->saveSetting();
                    }

                    //save sort
                    /** @var Admin_Form_TopNotificationForm $cateObject */
                    if(isset($params['sort']) && is_array($params['sort'])){
                        $cateObject = $this->view->form->getSubForm('edit');
                        $cateObject->massSort($hp->id,$params['sort']);
                    }

                    // $adapter->commit();
                    DB::commit();
                    return $this->success(['message' => $this->text->get('notification_settings.settings.success')]);
                    // $this->_responseJSON(200,$this->text->get('notification_settings.settings.success'));
                }
                catch (\Exception $e){
                    return $this->error($e->getMessage());
                    // $this->_responseJSON(400,$e->getMessage());
                    // $adapter->rollBack();
                    DB::rollback();
                }
                exit;
            }


            if(isset($params['create'])){
                //check create post
                $createArr = $params['create'];
                $createFormCheck = new TopNotificationForm(array(
                    'hp' => $hp,
                    'settings' => $settings,
                    'parentId' => $createArr['parent_page_id']
                ));
                $createFormCheck->setData($createArr);

                if(isset($params['preview']) && ($params['preview'] == true || $params['preview'] == 'true')){
                    if($createFormCheck->isValid($createArr)) {
                        return $this->success($this->text->get('notification_settings.create.success'));
                        // $this->_responseJSON(200,$this->text->get('notification_settings.create.success'));
                    } else {
                        return $this->success([
                            'message' => $this->text->get('notification_settings.create.error'),
                            'errors' => $createFormCheck->getMessages()
                        ], false, 400);
                    }
                }

                DB::beginTransaction();

                try {
                    // $adapter->beginTransaction();

                    if($createFormCheck->isValid($createArr)) {

                        $row = $createFormCheck->saveData();

                        // $adapter->commit();
                        DB::commit();
                        return $this->success(['data' => $row]);
                        // $this->_responseJSON(200,$this->text->get('notification_settings.create.success'), array(
                        //     'data' => $row
                        // ));
                    }
                    else {
                        $messages = $createFormCheck->getMessages();
                        DB::rollback();
                        // $this->error($this->text->get('notification_settings.create.error'), $messages);
                        return $this->success([
                            'message' => $this->text->get('notification_settings.create.error'),
                            'errors' => $messages
                        ], false, 400);
                        // $this->_responseJSON(400,$this->text->get('notification_settings.create.error'), array(
                        //     'errors' => $messages
                        // ));
                        // $adapter->rollback();
                    }
                } catch (\Exception $e) {
                    // $adapter->rollback();
                    DB::rollback();
                    return $this->error($e->getMessage());
                    // $this->_responseJSON(400,$e->getMessage());
                }
                // exit;
            }

            //check update post
            if(isset($params['edit'])){

                $updateArr = $params['edit'];

                /** @var Admin_Form_TopNotificationForm $formCheckUpdate */
                $formCheckUpdate = new TopNotificationForm(array(
                    'settings' => $settings,
                    'hp' => $hp,
                    'id' => $updateArr['id'],
                    'parentId' => $updateArr['parent_page_id']
                ));
                $formCheckUpdate->setData($updateArr);

                if($formCheckUpdate->isValid($updateArr)) {

                    // $adapter = $table->getAdapter();
                    DB::beginTransaction();
                    try {
                        // $adapter->beginTransaction();

                        $row = $formCheckUpdate->saveData();

                        // $adapter->commit();
                        DB::commit();
                        return $this->success(['data' => $row]);

                        // $this->_responseJSON(200,$this->text->get('notification_settings.update.success'),[
                        //     'data' => $row
                        // ]);


                    } catch (\Exception $e) {
                        // $adapter->rollback();
                        // $this->_responseJSON(400,$this->text->get('notification_settings.update.error'));
                        DB::rollback();
                        return $this->error($this->text->get('notification_settings.update.error'));
                    }
                }
                else {
                    $messages = $formCheckUpdate->getMessages();
                    // $this->_responseJSON(400,$this->text->get('notification_settings.update.error'), array(
                    //     'errors' => $messages
                    // ));
                    return $this->success([
                            'message' => $this->text->get('notification_settings.update.error'),
                            'errors' => $messages
                        ], false, 400);
                }
                // exit;
            }

            // check delete post
            if(isset($params['delete'])){

                $deleteArr = $params['delete'];

                DB::beginTransaction();
                try {
                    // $adapter->beginTransaction();

                    $this->view->form->getSubForm('delete')->deleteData($deleteArr['id']);

                    AssociatedHpPageAttribute::where(array(
                        'hp_id' => $hp->id,
                        'hp_main_parts_id' => $deleteArr['id']
                    ))->update(['delete_flg' => 1]);

                    // $adapter->commit();
                    DB::commit();
                    return $this->success(['data' => ['id' => $deleteArr['id']]]);

                    // $this->_responseJSON(200,$this->text->get('notification_settings.delete.success'),array(
                    //     'data' => array( 'id' => $deleteArr['id'] )
                    // ));


                } catch (\Exception $e) {
                    // $adapter->rollback();
                    // $this->_responseJSON(400,$this->text->get('notification_settings.delete.error'));
                    DB::rollback();
                    return $this->error($this->text->get('notification_settings.delete.error'));
                }

                // exit;
            }

            // $this->_responseJSON(400,'');
            return $this->error('');
        }

        $this->view->hp = $hp;
        $this->view->params = $params;
        return view('admin::company.top-notification');
    }

    /**
    * 09_List File Edit
    * 09_契約管理:編集ファイル一覧
    * Manage files upload
    *
    * @return json_encode || void
    */
    public function topListFileEdit(Request $request)
    {
        $original = new Original();
        $di = $this->getCurrentDirectory($request);
        
        $params = $request->all();
        $company_id = isset($params['company_id']) ? $params['company_id'] : '';

        if ('' == $company_id) {
            throw new Exception( "No Company Data. " );
            exit;
        }

        $row = $this->_checkCompanyTOP($company_id);
        $isAdmin = getInstanceUser('admin')->checkIsSuperAdmin(getInstanceUser('admin')->getProfile());
        $isAgency = getInstanceUser('admin')->isAgency();

        if ($this->checkRedirectTopOriginal($row, $company_id, $isAdmin, $isAgency)) {
            return redirect('/admin/company/detail?id='.$company_id);
        }

        $sub_dir = isset($params['sub_dir']) ? $params['sub_dir'] : '';

        $sortByDate = isset($params['field']) && $params['field'] == 'date';
        $sortOrderBy = isset($params['orderby']) && $params['orderby'] == 'desc';

        $data = new stdClass();

        $data->nlist      = $sortByDate ? $di->getListByDate($sortOrderBy) : $di->getList($sortOrderBy);
        $data->sub_dir    = $sub_dir;
        $data->company_id = $company_id;
        
        foreach ($data->nlist as $item) {
          $item->hasContextMenu = false;
          if ($item->isFile && ($item->data['can_edit_name'] || $item->data['can_edit_data'])) $item->hasContextMenu = true;
          $item->isSpecialFile = $di->checkIsSpecialFile($item->name);
        }

        $params['backTOP'] = $original->getScreenUrl(config('constants.original.ORIGINAL_EDIT_CMS'),$company_id);
        $params['backCurrent'] = $original->getScreenUrl(config('constants.original.ORIGINAL_EDIT_FILE'),$company_id);
        $params['isRoot'] = (isset($params['sub_dir']) && $params['sub_dir'] == config('constants.original.ORIGINAL_IMPORT_TOPROOT'))
          || !isset($params['sub_dir']) ? true : false;

        $sub_dir_param = (isset($params['sub_dir']) && $params['sub_dir'] == config('constants.original.ORIGINAL_IMPORT_TOPROOT'))
        || !isset($params['sub_dir']) ? '' : '&sub_dir=' . $params['sub_dir'];
        $params['currentUrl'] = $params['backCurrent'].$sub_dir_param ;

        $params['warnings'] = $this->text->getMultiKeyValue(array(
            'list_file_edit.warning_html',
            'list_file_edit.warning_2',
            'list_file_edit.warning_3',
        ),'list_file_edit.warning_title_change_key');

        $view_title = $original->getScreenTitle(config('constants.original.ORIGINAL_EDIT_FILE'));

        if(isset($params['sub_dir'])){
            $params['warnings'] = array();
            switch($params['sub_dir']){
                case config('constants.original.ORIGINAL_IMPORT_TOPCSS'):
                    $params['warnings'] = $this->text->getMultiKeyValue(array(
                        'list_file_edit.warning_css',
                        'list_file_edit.warning_2',
                        'list_file_edit.warning_3',
                    ),'list_file_edit.warning_title_change_key');
                    $view_title = 'top_cssフォルダ';
                    break;
                case config('constants.original.ORIGINAL_IMPORT_TOPJS'):
                    $params['warnings'] = $this->text->getMultiKeyValue(array(
                        'list_file_edit.warning_js',
                        'list_file_edit.warning_2',
                        'list_file_edit.warning_3',
                    ),'list_file_edit.warning_title_change_key');
                    $view_title = 'top_jsフォルダ';
                    break;
                case config('constants.original.ORIGINAL_IMPORT_TOPIMAGE'):
                    $params['warnings'] = $this->text->getMultiKeyValue(array(
                        'list_file_edit.warning_image',
                        'list_file_edit.warning_2',
                        'list_file_edit.warning_3',
                    ),'list_file_edit.warning_title_change_key');
                    $view_title = 'top_imageフォルダ';
                    break;
                case config('constants.original.ORIGINAL_IMPORT_TOPKOMA'):
                    $params['warnings'] = $this->text->getMultiKeyValue(array(
                        'list_file_edit.warning.koma_1',
                        'list_file_edit.warning.koma_2',
                        'list_file_edit.warning.koma_3',
                    ),'list_file_edit.warning_title_change_key');
                    $view_title = 'bukken_komaフォルダ';
                    break;
                default:
            }
        }

        $this->breadcrumbTOPEdit($view_title, $company_id);
        
        $this->view->data = $data;
        $this->view->params = $params;
        return view('admin::company.top-list-file-edit');
    }

    /*
   * return directory working for manage file and folder upload
   *
   * @return class Library\Custom\DirectoryIterator
   */
    private function getCurrentDirectory(Request $request)
    {
        // get request params
        $params = $request->all();
        $company_id = isset($params['company_id']) ? $params['company_id'] : '';
        $sub_dir = isset($params['sub_dir']) ? $params['sub_dir'] : '';
        
        if('' == $company_id || !is_numeric($company_id)) {
          return;
        }
        
        $original = new Original();
        $rootDir = $original->getOriginalImportPath($company_id);
        $rootRedirect = $original->getOriginalImportUrl($company_id);
        $dataInfo = $original->getOriginalImportDataInfo($company_id);
        
        $uploadDir = $rootDir;
        $redirectTo = $rootRedirect;
        
        $isSubDir =  array_key_exists($sub_dir, $dataInfo);
        if ($isSubDir) {
          $uploadDir = $dataInfo[$sub_dir]['direction'];
          $redirectTo = $dataInfo[$sub_dir]['link'];
        }
        
        $di = new DirectoryIterator(true);

        // initial folders
        $di->load($uploadDir);
        
        if ($uploadDir == $rootDir) {
            $di->initialImportHtmlDir($company_id);
          $data = $dataInfo[config('constants.original.ORIGINAL_IMPORT_TOPROOT')];

          $di->setExtensions($data['accepted_exts']);
          $di->setSpecialFiles($data['accepted_files'], $data['extra_files']);
        
          foreach ($dataInfo as $key => $info) {
            $di->setDataFile($key, $info);
          }
        } else if($isSubDir) {
            $di->fakeFolderOriginal($uploadDir, '..');
          $data = $dataInfo[$sub_dir];
          
          $di->setExtensions($data['accepted_exts']);
          $di->ignoreRootDir(false);
          if (isset($data['accepted_files']) && count($data['accepted_files']) > 0) {
            $di->setSpecialFiles($data['accepted_files'], $data['extra_files']);
          }
          $di->setData($data);
        }
        $di->setRootUrl($rootRedirect);
        return $di;
    }

    /**
    * Api save file action
    *
    * @return json_encode || void
    */
    public function apiSaveFile(Request $request)
    {
        $di = $this->getCurrentDirectory($request);
        $sub_dir = $request->has('sub_dir') ? $request->get('sub_dir') : '';
        $originalFile = $request->get('original_file') ?: '';
        $res = array('success' => 1, 'data' => ['Ok' => true]);
        
        if ($originalFile && $request->has('change_name')) {
            $fileName = $request->get('change_name');
            if (false == $di->updateFileName($originalFile, $fileName)) {
                
                if ('' != $di->getMessageError()) {
                    $res['success'] = 0;
                    return $this->success($res);
                }

                $errors = [
                    'DUPLICATE' => 'フォルダ内に同じ名前のファイルが存在しています。変更してください。',
                    config('constants.original.ORIGINAL_IMPORT_TOPCSS') => 'このフォルダには、.cssファイルをアップロードしてください。',
                    config('constants.original.ORIGINAL_IMPORT_TOPJS') => 'このフォルダには、.jsファイルをアップロードしてください。',
                    config('constants.original.ORIGINAL_IMPORT_TOPIMAGE') => 'このフォルダには、画像形式のファイルをアップロードしてください。',
                ];
                        
                if (false == $di->checkIsValidName($fileName)) {
                    // $res['data']['error'] ='Invalid file name.';
                } else if (false == $di->checkIsAllowExtension($fileName)) {
                    if ('' != $sub_dir && isset($errors[$sub_dir])) {
                        $res['data']['error'] = $errors[$sub_dir];
                    }
                } else if ($di->checkIsExistFile($fileName)) {
                    $res['data']['error'] = $errors['DUPLICATE'];
                }
            }
            
            return $this->error($res);
        }
        
        if ($request->has('revert_content')) {
            if (false == $di->revertFileContent($request->get('revert_content'))) {
                if ('' != $di->getMessageError()) {
                    $res['success'] = 0;
                    return $this->success($res);
                }
                
            }
            return $this->success($res);
        }
        
        if ($request->has('remove_content')) {
          if (false == $di->removeFile($request->get('remove_content'))) {
            if ('' != $di->getMessageError()) {
                $res['success'] = 0;
                return $this->success($res);
            }
                
          }
          return $this->success($res);
        }
        
        if ($originalFile && $request->has('change_content')) {
          if (false == $di->updateFileContent($originalFile, $request->get('change_content'))) {
            if ('' != $di->getMessageError()) {
                $res['success'] = 0;
                return $this->success($res);
            }
                
          }
          return $this->success($res);
        }
        return $this->error(array('status'=> 0, 'errors'=> 'Invalid params.'));
    }

    /**
    * function turn on flag to force stop progress
    *
    */
    public function apiSynchronizeUploadProgress(Request $request)
    {
        $params = $request->all();
        $isSuccess = isset($params['isSuccess']) ? (int)$params['isSuccess'] : 0;
        
        $di = $this->getCurrentDirectory($request);

        if (1 === $isSuccess) {
            $di->mergeDir();
            $data = array('success'=> 1, 'data'=> 'Ok');
            return $this->success($data);
        }
        $di->removeTmpDir();
        $data = array('success'=> 2, 'data'=> 'Ok');
        return $this->success($data);
    }

    /**
    * Manage files upload
    *
    * @return json_encode || void
    */
    public function apiUploadFile(Request $request)
    {
        // $this->_helper->layout->disableLayout();
        // $this->_helper->viewRenderer->setNoRender();

        $errors = [];
        if ($request->isMethod('post')) {
            $di = $this->getCurrentDirectory($request);
            $di->removeTmpDir(); // remove old tmp files
            
            if ($request->hasfile('file')) {
                foreach ($request->file('file') as $file) {
                    $name = $file->getClientOriginalName();
                    if (in_array($file->getClientOriginalExtension(), $di->getExtensions())) {
                        
                        $pathFile = $di->getTmpDestination().'/'.$name;
                        \Storage::disk('s3')->put($di->pathS3($pathFile), file_get_contents($file));
                    }
                }
            }
            // $upload = new Zend_File_Transfer_Adapter_Http();
            // $upload->setDestination($di->getTmpDestination())
            //   // ->addValidator('Size', false, 8388608)
            //   // ->addValidator('Filessize', false, 8388608)
            //   ->addValidator('Extension', false, $di->getExtensions());

            // $files  = $upload->getFileInfo();

            // foreach ($files as $file => $fileInfo) {
            //     if ($upload->isUploaded($file)) {
            //         if ($upload->isValid($file) && $di->checkIsValidFile($fileInfo['name'])) {
            //             $upload->receive($file);
            //         }
            //     }
            // }
        }
        
        $data = array(
            'success'=> 1,
            'message'=> 'Ok',
            'errors'=> $errors,
        );
        return $this->success($data);
    }

    /**
     * タグ設定用
     */
    public function tag(Request $request, $company_id = '')
    {
        $this->checkUserRules();

        if (empty($company_id)) {
            if ($request->has('company_id')) {
                $company_id = $request->company_id;
            }
        }

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $controller = $this->_controller;
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }
        $this->view->company = $row;

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $controller);
        $this->view->topicPath("契約者詳細", "detail", $controller, array("id" => $company_id));
        $this->view->topicPath($original_tag);

        $tagObj = $this->tagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyGoogleAnalyticsTag(), 'google');

        if ($request->has("submit") && $request->submit != "") {

            //契約が「評価・分析のみ契約」の場合は必須を外す
            if (isset($params['google']['id']) && $params['google']['id'] != "") {
                foreach ($form->getSubForm('google')->getElements() as $key => $element) {
                    if ($key == "file_name") $element->setRequired(false);
                }
            }

            $form->setData($params);
            // バリデーション
            if (!$form->isValid($params)) {
                //submit削除
                $form->setData($params);
                $form->getMessages();
                $request->input("submit", "");
                $request->input("back", "");
                $this->view->params = $params;
                return view('admin::company.tag');
            }
            $form->setData($params);
            $this->view->params = $params;
            return view('admin::company.tag-cnf');
        } else if ($request->has("back") || $request->back) {
            unset($params['back']);
            $form->setData($params);
        } else {

            $row = $tagObj->getDataForCompanyId($company_id);
            if ($row != null || $row != false) {
                $row_data = $row->toArray();
                $form->setData($row_data);
            }
        }
        $form->setData($params);
        $this->view->params = $params;

        return view('admin::company.tag');
    }

    /**
     * タグ設定用（確認）
     */
    public function tagCnf(Request $request, $company_id)
    {
        $this->checkUserRules();

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $controller = $this->_controller;
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $controller);
        $this->view->topicPath("契約者詳細", "detail", $controller, array("id" => $company_id));
        $this->view->topicPath($original_tag);

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        //マスターで取る
        $tagObj = $this->tagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyGoogleAnalyticsTag(), 'google');

        if ($request->has("submit") && $request->submit != "") {

            $data = array();
            $data['company_id']      = $params['company_id'];
            $data['google_user_id']  = $params['google']['google_user_id'];
            $data['google_password'] = $params['google']['google_password'];

            //URLとかを取得
            if ($params['google']['file_name'] != "") {
                $conf = getConfigs('admin.FileUploadServer');
                $p12_data = @file_get_contents($conf->upload->admin_url . $params['company_id']  . "/google/" . $params['google']['file_name']);
                if ($p12_data === false) {
                    throw new Exception("No File Error");
                    exit;
                }
                $data['google_p12'] = $p12_data;
            }

            $data['google_analytics_mail']    = $params['google']['google_analytics_mail'];
            $data['google_analytics_view_id'] = $params['google']['google_analytics_view_id'];
            $data['google_analytics_code']    = $params['google']['google_analytics_code'];

            // $data['above_close_head_tag']  = $params['other']['above_close_head_tag'];
            // $data['under_body_tag']        = $params['other']['under_body_tag'];
            // $data['above_close_body_tag']  = $params['other']['above_close_body_tag'];

            try {
                DB::beginTransaction();

                //更新
                if (isset($params['google']['id']) && $params['google']['id'] != "" && is_numeric($params['google']['id'])) {
                    $where = array(["id", $params['google']['id']]);
                    $tagObj->update($where, $data);
                    //新規
                } else {
                    $id = $tagObj->create($data);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            return redirect('/admin/company/tag-cmp/company_id/' . $company_id);
        } else if ($request->has("back") && $request->back != "") {
            return redirect('/admin/company/tag/company_id/' . $company_id);
        }

        $form->setData($params);
        $this->view->params = $params;
        return view('admin::company.tag-cnf');
    }

    /**
     * タグ設定用（完了）
     */
    public function tagCmp(Request $request, $company_id)
    {
        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;
        $this->view->params = $params;

        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);

        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath($original_tag);

        return view('admin::company.tag-cmp');
    }
    /**
     * その他タグ設定用
     */
    public function otherTag(Request $request, $company_id)
    {
        $this->checkUserRules();

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }
        $this->view->company = $row;

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath($original_tag);

        $tagObj = $this->tagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyTag(), 'other');

        if ($request->has("submit") && $request->submit != "") {

            $form->setData($params);
            //バリデーション
            if (!$form->isValid($params)) {
                //submit削除
                $form->setData($params);
                $form->getMessages();
                $request->input("submit", "");
                $request->input("back", "");
                $this->view->params = $params;
                return view('admin::company.other-tag');
            }
            $form->setData($params);
            $this->view->params = $params;
            return view('admin::company.other-tag-cnf');
        } else if ($request->has("back") || $request->back) {
            unset($params['back']);
            $form->setData($params);
        } else {

            $row = $tagObj->getDataForCompanyId($company_id);
            if ($row != null || $row != false) {
                $row_data = $row->toArray();
                $form->setData($row_data);
            }
        }
        $form->setData($params);
        $this->view->params = $params;

        return view('admin::company.other-tag');
    }

    /**
     * タグ設定用（確認）
     */
    public function otherTagCnf(Request $request, $company_id)
    {
        $this->checkUserRules();

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath($original_tag);

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        //マスターで取る
        $tagObj = $this->tagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyTag(), 'other');

        if ($request->has("submit") && $request->submit != "") {
            $data = array();
            $data['company_id'] = $params['other']['company_id'];
            $data['above_close_head_tag'] = $params['other']['above_close_head_tag'];
            $data['under_body_tag'] = $params['other']['under_body_tag'];
            $data['above_close_body_tag'] = $params['other']['above_close_body_tag'];
            $data['above_close_head_tag_contact_thanks'] = $params['other']['above_close_head_tag_contact_thanks'];
            $data['under_body_tag_contact_thanks'] = $params['other']['under_body_tag_contact_thanks'];
            $data['above_close_body_tag_contact_thanks'] = $params['other']['above_close_body_tag_contact_thanks'];
            $data['above_close_head_tag_assess_thanks'] = $params['other']['above_close_head_tag_assess_thanks'];
            $data['under_body_tag_assess_thanks'] = $params['other']['under_body_tag_assess_thanks'];
            $data['above_close_body_tag_assess_thanks'] = $params['other']['above_close_body_tag_assess_thanks'];
            $data['above_close_head_tag_request_thanks'] = $params['other']['above_close_head_tag_request_thanks'];
            $data['under_body_tag_request_thanks'] = $params['other']['under_body_tag_request_thanks'];
            $data['above_close_body_tag_request_thanks'] = $params['other']['above_close_body_tag_request_thanks'];
            $data['above_close_head_tag_contact_input'] = $params['other']['above_close_head_tag_contact_input'];
            $data['under_body_tag_contact_input'] = $params['other']['under_body_tag_contact_input'];
            $data['above_close_body_tag_contact_input'] = $params['other']['above_close_body_tag_contact_input'];
            $data['above_close_head_tag_assess_input'] = $params['other']['above_close_head_tag_assess_input'];
            $data['under_body_tag_assess_input'] = $params['other']['under_body_tag_assess_input'];
            $data['above_close_body_tag_assess_input'] = $params['other']['above_close_body_tag_assess_input'];
            $data['above_close_head_tag_request_input'] = $params['other']['above_close_head_tag_request_input'];
            $data['under_body_tag_request_input'] = $params['other']['under_body_tag_request_input'];
            $data['above_close_body_tag_request_input'] = $params['other']['above_close_body_tag_request_input'];

            try {
                DB::beginTransaction();

                //更新
                if (isset($params['other']['id']) && $params['other']['id'] != "" && is_numeric($params['other']['id'])) {
                    $where = array(["id", $params['other']['id']]);
                    $tagObj->update($where, $data);

                    //新規
                } else {
                    $id = $tagObj->create($data);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

            return redirect('/admin/company/tag-cmp/company_id/' . $company_id);
        } else if ($request->has("back") && $request->back != "") {
            return redirect('/admin/company/other-tag/company_id/' . $company_id);
        }

        $form->setData($params);
        $this->view->params = $params;

        return view('admin::company.other-tag-cnf');
    }

    /**
     * 物件用その他タグ設定用
     */
    public function otherEstateTag(Request $request, $company_id)
    {
        $this->checkUserRules();

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }
        $this->view->company = $row;

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath($original_tag);

        $tagObj = $this->estateTagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyEstateTag(), 'other');

        if ($request->has("submit") && $request->submit != "") {

            $form->setData($params);
            //バリデーション
            if (!$form->isValid($params)) {
                //submit削除
                $form->setData($params);
                $form->getMessages();
                $request->input("submit", "");
                $request->input("back", "");
                $this->view->params = $params;
                return view('admin::company.other-estate-tag');
            }
            $form->setData($params);
            $this->view->params = $params;
            return view('admin::company.other-estate-tag-cnf');
        } else if ($request->has("back") || $request->back) {
            unset($params['back']);
            $form->setData($params);
        } else {

            $row = $tagObj->getDataForCompanyId($company_id);
            if ($row != null || $row != false) {
                $row_data = $row->toArray();
                $form->setData($row_data);
            }
        }
        $form->setData($params);
        $this->view->params = $params;

        return view('admin::company.other-estate-tag');
    }

    /**
     * 物件用タグ設定用（確認）
     */
    public function otherEstateTagCnf(Request $request, $company_id)
    {
        $this->checkUserRules();

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath($original_tag);

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        //マスターで取る
        $tagObj = $this->estateTagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyEstateTag(), 'other');

        if ($request->has("submit") && $request->submit != "") {

            $data = array();
            $data['company_id']      = $params['company_id'];
            $data['above_close_head_tag_residential_rental_thanks']  = $params['other']['above_close_head_tag_residential_rental_thanks'];
            $data['under_body_tag_residential_rental_thanks'] = $params['other']['under_body_tag_residential_rental_thanks'];
            $data['above_close_body_tag_residential_rental_thanks'] = $params['other']['above_close_body_tag_residential_rental_thanks'];
            $data['above_close_head_tag_business_rental_thanks'] = $params['other']['above_close_head_tag_business_rental_thanks'];
            $data['under_body_tag_business_rental_thanks'] = $params['other']['under_body_tag_business_rental_thanks'];
            $data['above_close_body_tag_business_rental_thanks'] = $params['other']['above_close_body_tag_business_rental_thanks'];
            $data['above_close_head_tag_residential_sale_thanks'] = $params['other']['above_close_head_tag_residential_sale_thanks'];
            $data['under_body_tag_residential_sale_thanks'] = $params['other']['under_body_tag_residential_sale_thanks'];
            $data['above_close_body_tag_residential_sale_thanks'] = $params['other']['above_close_body_tag_residential_sale_thanks'];
            $data['above_close_head_tag_business_sale_thanks'] = $params['other']['above_close_head_tag_business_sale_thanks'];
            $data['under_body_tag_business_sale_thanks'] = $params['other']['under_body_tag_business_sale_thanks'];
            $data['above_close_body_tag_business_sale_thanks'] = $params['other']['above_close_body_tag_business_sale_thanks'];
            $data['above_close_head_tag_residential_rental_input'] = $params['other']['above_close_head_tag_residential_rental_input'];
            $data['under_body_tag_residential_rental_input'] = $params['other']['under_body_tag_residential_rental_input'];
            $data['above_close_body_tag_residential_rental_input'] = $params['other']['above_close_body_tag_residential_rental_input'];
            $data['above_close_head_tag_business_rental_input'] = $params['other']['above_close_head_tag_business_rental_input'];
            $data['under_body_tag_business_rental_input'] = $params['other']['under_body_tag_business_rental_input'];
            $data['above_close_body_tag_business_rental_input'] = $params['other']['above_close_body_tag_business_rental_input'];
            $data['above_close_head_tag_residential_sale_input'] = $params['other']['above_close_head_tag_residential_sale_input'];
            $data['under_body_tag_residential_sale_input'] = $params['other']['under_body_tag_residential_sale_input'];
            $data['above_close_body_tag_residential_sale_input'] = $params['other']['above_close_body_tag_residential_sale_input'];
            $data['above_close_head_tag_business_sale_input'] = $params['other']['above_close_head_tag_business_sale_input'];
            $data['under_body_tag_business_sale_input'] = $params['other']['under_body_tag_business_sale_input'];
            $data['above_close_body_tag_business_sale_input'] = $params['other']['above_close_body_tag_business_sale_input'];

            try {
                DB::beginTransaction();

                //更新
                if (isset($params['other']['id']) && $params['other']['id'] != "" && is_numeric($params['other']['id'])) {
                    $where = array(["id", $params['other']['id']]);
                    $tagObj->update($where, $data);
                    //新規
                } else {
                    $id = $tagObj->create($data);
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }

            return redirect('/admin/company/tag-cmp/company_id/' . $company_id);
        } else if ($request->has("back") && $request->back != "") {
            return redirect('/admin/company/other-estate-tag/company_id/' . $company_id);
        }

        $form->setData($params);
        $this->view->params = $params;

        return view('admin::company.other-estate-tag-cnf');
    }

    /**
     * 物件用その他タグ設定用
     */
    public function otherEstateRequestTag(Request $request, $company_id)
    {
        $this->checkUserRules();

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }
        $this->view->company = $row;

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath($original_tag);

        $tagObj = $this->estateRequestTagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyEstateRequestTag(), 'other');

        if ($request->has("submit") && $request->submit != "") {

            $form->setData($params);
            //バリデーション
            if (!$form->isValid($params)) {
                //submit削除
                $form->setData($params);
                $form->getMessages();
                $request->input("submit", "");
                $request->input("back", "");
                $this->view->params = $params;
                return view('admin::company.other-estate-request-tag');
            }
            $form->setData($params);
            $this->view->params = $params;
            return view('admin::company.other-estate-request-tag-cnf');
        } else if ($request->has("back") || $request->back) {
            unset($params['back']);
            $form->setData($params);
        } else {

            $row = $tagObj->getDataForCompanyId($company_id);
            if ($row != null || $row != false) {
                $row_data = $row->toArray();
                $form->setData($row_data);
            }
        }
        $form->setData($params);
        $this->view->params = $params;

        return view('admin::company.other-estate-request-tag');
    }

    /**
     * 物件用タグ設定用（確認）
     */
    public function otherEstateRequestTagCnf(Request $request, $company_id)
    {
        $this->checkUserRules();

        if (!isset($company_id) || $company_id == "" || !is_numeric($company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }

        //オブジェクト取得
        $companyObj = $this->companyRepository;
        $row = $companyObj->getDataForId($company_id);
        if ($row == null) {
            throw new Exception("No Company Data. ");
            exit;
        }

        $this->view->original_tag = $original_tag = Original::getEffectMeasurementTitle();

        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath($original_tag);

        //パラメータ取得
        $params = $request->all();
        $params['company_id'] = $company_id;

        //マスターで取る
        $tagObj = $this->estateRequestTagRepository;

        //フォーム設定
        $this->view->form = $form = new Form();
        $form->addSubForm(new CompanyEstateRequestTag(), 'other');

        if ($request->has("submit") && $request->submit != "") {
            $data = array();
            $data['company_id'] = $params['other']['company_id'];
            $data['above_close_head_tag_residential_rental_request_thanks'] = $params['other']['above_close_head_tag_residential_rental_request_thanks'];
            $data['under_body_tag_residential_rental_request_thanks'] = $params['other']['under_body_tag_residential_rental_request_thanks'];
            $data['above_close_body_tag_residential_rental_request_thanks'] = $params['other']['above_close_body_tag_residential_rental_request_thanks'];
            $data['above_close_head_tag_business_rental_request_thanks'] = $params['other']['above_close_head_tag_business_rental_request_thanks'];
            $data['under_body_tag_business_rental_request_thanks'] = $params['other']['under_body_tag_business_rental_request_thanks'];
            $data['above_close_body_tag_business_rental_request_thanks'] = $params['other']['above_close_body_tag_business_rental_request_thanks'];
            $data['above_close_head_tag_residential_sale_request_thanks'] = $params['other']['above_close_head_tag_residential_sale_request_thanks'];
            $data['under_body_tag_residential_sale_request_thanks'] = $params['other']['under_body_tag_residential_sale_request_thanks'];
            $data['above_close_body_tag_residential_sale_request_thanks'] = $params['other']['above_close_body_tag_residential_sale_request_thanks'];
            $data['above_close_head_tag_business_sale_request_thanks'] = $params['other']['above_close_head_tag_business_sale_request_thanks'];
            $data['under_body_tag_business_sale_request_thanks'] = $params['other']['under_body_tag_business_sale_request_thanks'];
            $data['above_close_body_tag_business_sale_request_thanks'] = $params['other']['above_close_body_tag_business_sale_request_thanks'];
            $data['above_close_head_tag_residential_rental_request_input'] = $params['other']['above_close_head_tag_residential_rental_request_input'];
            $data['under_body_tag_residential_rental_request_input'] = $params['other']['under_body_tag_residential_rental_request_input'];
            $data['above_close_body_tag_residential_rental_request_input'] = $params['other']['above_close_body_tag_residential_rental_request_input'];
            $data['above_close_head_tag_business_rental_request_input'] = $params['other']['above_close_head_tag_business_rental_request_input'];
            $data['under_body_tag_business_rental_request_input'] = $params['other']['under_body_tag_business_rental_request_input'];
            $data['above_close_body_tag_business_rental_request_input'] = $params['other']['above_close_body_tag_business_rental_request_input'];
            $data['above_close_head_tag_residential_sale_request_input'] = $params['other']['above_close_head_tag_residential_sale_request_input'];
            $data['under_body_tag_residential_sale_request_input'] = $params['other']['under_body_tag_residential_sale_request_input'];
            $data['above_close_body_tag_residential_sale_request_input'] = $params['other']['above_close_body_tag_residential_sale_request_input'];
            $data['above_close_head_tag_business_sale_request_input'] = $params['other']['above_close_head_tag_business_sale_request_input'];
            $data['under_body_tag_business_sale_request_input'] = $params['other']['under_body_tag_business_sale_request_input'];
            $data['above_close_body_tag_business_sale_request_input'] = $params['other']['above_close_body_tag_business_sale_request_input'];

            try {
                DB::beginTransaction();

                //更新
                if (isset($params['other']['id']) && $params['other']['id'] != "" && is_numeric($params['other']['id'])) {
                    $where = array(["id", $params['other']['id']]);
                    $tagObj->update($where, $data);

                    //新規
                } else {
                    $id = $tagObj->create($data);
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            return redirect('/admin/company/tag-cmp/company_id/' . $company_id);
        } else if ($request->has("back") && $request->back != "") {
            return redirect('/admin/company/other-estate-request-tag/company_id/' . $company_id);
        }
        $form->setData($params);
        $this->view->params = $params;

        return view('admin::company.other-estate-request-tag-cnf');
    }
    
    public function estateGroup(Request $request)
    {
        $this->checkUserRules();
        // redirect company Lite
        $companyObj = $this->companyRepository ;
        $row = $companyObj->getDataForId($request->company_id);
        if ($row->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') AND $row->reserve_cms_plan <= config('constants.cms_plan.CMS_PLAN_LITE')){
            return redirect ("/admin/company");
        }
        if(!$request->has("company_id") || $request->company_id == "" || !is_numeric($request->company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }
        //パラメータ取得
        $params = $request->all();
        //パンクズ設定
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $pan_arr = array("id" => $request->company_id);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, $pan_arr);
        $this->view->topicPath("物件グループ設定");
        //オブジェクト取得
        $parentCompanyObj = $this->companyRepository;
        $parentCompanyRow = $parentCompanyObj->getDataForId($request->company_id);
        if($parentCompanyRow == null) {
            throw new Exception("No Company Data. ");
            exit;
        }
        $this->view->company = $parentCompanyRow;
        try {
            DB::beginTransaction();
            $estateAccosiateObj = $this->estateAssociatedCompanyRepository;
            // 追加の場合
            if($request->has("add_member_no") && $request->add_member_no != "" || is_numeric($request->add_member_no)) {
                $data = array();
                $data['parent_company_id']    = $parentCompanyRow->id;
                $data['subsidiary_member_no'] = $request->add_member_no;
                $estateAccosiateObj->create($data);
                DB::commit();
                return redirect("/admin/company/estate-group/?company_id=".$parentCompanyRow->id);
            }
            // 物件グループの一覧を取得する
            $estateGroup = new Group();
            $companies = $estateGroup->getSubCompanies($parentCompanyRow->id);
            $this->view->companies = $companies;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        return view('admin::company.estate-group')->with('params', $params);
    }
    public function estateGroupDel(Request $request)
    {
        if(!$request->has("del_member_no") || $request->del_member_no == "" || !is_numeric($request->del_member_no)) {
            throw new Exception("No member ID. ");
            exit;

        }else if(!$request->has("del_associate_id") || $request->del_associate_id == "" || !is_numeric($request->del_associate_id)) {
            throw new Exception("No Del ID. ");
            exit;

        }else if(!$request->has("del_parent_company_id") || $request->del_parent_company_id == "" || !is_numeric($request->del_parent_company_id)) {
            throw new Exception("No Parent Compnay id. ");
            exit;
        }
        $associate_id         = $request->del_associate_id;  
        $parent_company_id    = $request->del_parent_company_id;
        $subsidiary_member_no = $request->del_member_no;
        try {
            DB::beginTransaction();
            $estateAccosiateObj = $this->estateAssociatedCompanyRepository;
            $data = array();
            $data['delete_flg'] = 1;
            $where = [["id", $associate_id], ["parent_company_id", $parent_company_id ], ["subsidiary_member_no", $subsidiary_member_no ]];
            $estateAccosiateObj->update($where, $data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        return redirect("/admin/company/estate-group/?company_id=".$parent_company_id );
        exit;
    }

    /**
	 * CSV出力用
	 */
    public function csv()
    {
		$rows = $this->companyRepository->fetchAll();
		$rows_arr = $rows->toArray();

		$accountObj   = App::make(CompanyAccountRepositoryInterface::class);
		$assCompHpObj = App::make(AssociatedCompanyHpRepositoryInterface::class);
		$hpObj        = App::make(HpPageRepositoryInterface::class);
		$logDelObj    = App::make(LogDeleteRepositoryInterface::class);
		$secondEstateObj	= App::make(SecondEstateRepositoryInterface::class);
        $originalSettingObj = App::make(OriginalSettingRepositoryInterface::class);
        $associatedCompanyFdpObj = App::make(AssociatedCompanyFdpRepositoryInterface::class);
        $associatedCompanyFdps = empty($associatedCompanyFdpObj->fetchAll()->toArray()) ? '' : $associatedCompanyFdpObj->fetchAll()->toArray();
        $companyFdpList = [];
        if ($associatedCompanyFdps) {
            foreach ($associatedCompanyFdps as $associatedCompanyFdp) {
                $companyFdpList[$associatedCompanyFdp['company_id']] = $associatedCompanyFdp;
            }
        }

		//CSV対象カラム名
		$csv_header = CompanyCsvDownloadHeader::getCsvHeader();

		//CSV表示カラム名
		$csv_header_name = CompanyCsvDownloadHeader::getCsvHeaderName();

		// 出力
		$fileName = "keiyaku.csv";
		header("Pragma: public");
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $fileName);

	    $stream = fopen('php://output', 'w');
		$csv_row_name = array();
		foreach($csv_header_name as $name) {
			mb_convert_variables('SJIS-win', 'UTF-8', $name);
			$csv_row_name[] = $name;
		}

		fputcsv($stream, $csv_row_name);

		$agree_anmes = CompanyAgreementType::getInstance()->getAll();

		$csvs = array();
		foreach($rows_arr as $key => $val) {

			//やはり全部出す
			//if($val['contract_type'] != config('constants.company_agreement_type.CONTRACT_TYPE_PRIME')) continue;

			//CMSのログイン情報を取得する
			$accountRow = $accountObj->getDataForCompanyId($val['id']);

			//HP_PAGEの状況を確認する
			$assCompHpRow = $assCompHpObj->fetchRowByCompanyId($val['id']);

			// ２次広告の状況を確認する
			$secondEstateRow	= $secondEstateObj->getDataForCompanyId(	$val[ 'id' ] ) ;
			
            $originalSettingRow = $originalSettingObj->getDataForCompanyId($val['id']);

			$csv_row = array();
			foreach($csv_header as $name) {
				if( in_array( $name, array( 'reserve_applied_start_date','reserve_start_date','applied_end_date','end_date', 'initial_start_date', 'applied_start_date', 'start_date', 'map_applied_start_date', 'map_start_date', 'map_applied_end_date', 'map_end_date' ) ) ) {
					$date_view_name = $name ."_view";
					$val[$name] = $rows[$key]->$date_view_name;

				}else if($name == "contract_type") {
					$val[$name] = $agree_anmes[$val['contract_type']];
				}
				
				if ( ( $secondEstateRow !== null ) && ( strpos( $name, 'second_estate_') === 0 ) )
				{	// 「second_estate_」から始まっている場合
					$columnName		= substr( $name, 14 )	;
					$val[ $name ]	= $secondEstateRow->$columnName	;
				}
				
                if (($originalSettingRow !== null) && (strpos($name, 'original_setting_') === 0)) {
                    $columnName = str_replace('original_setting_', '', $name);
                    $val[$name] = $originalSettingRow->$columnName;
                }

                if ($companyFdpList && isset($companyFdpList[$val['id']]) && strpos($name, 'fdp_') === 0) {
                    $columnName = str_replace('fdp_', '', $name);
                    // change condition display FDP
                    if (!(substr($companyFdpList[$val['id']][$columnName], 0, 10) == "0000-00-00")) {
                        $val[$name] = $companyFdpList[$val['id']][$columnName];
                    }
                }

				if ( $name == 'contract_status' )
				{
					$value = $rows[ $key ]->isAvailable()																?  1	: 2			;
					$value = $rows[ $key ]->contract_type == config('constants.CompanyAgreementType.CONTRACT_TYPE_ANALYZE')	? '-'	: $value	;
					$val[ $name ] = $value	;
				}
				
				if ( ( $name == "cms_plan" ) || ( $name == "reserve_cms_plan" ) )
				{
					switch ( $val[ $name ] )
					{
						case config('constants.cms_plan.CMS_PLAN_ADVANCE')	: $value =	1 ; break ;
						case config('constants.cms_plan.CMS_PLAN_STANDARD')	: $value =	2 ; break ;
                        case config('constants.cms_plan.CMS_PLAN_LITE')     : $value =	3 ; break ;
						default											    : $value = '' ; break ;
					}
					$val[ $name ] = $value	;
				}

				mb_convert_variables('SJIS-win', 'UTF-8', $val[$name]);
				if($name == "ftp_password" || $name == "cp_password") {
					$csv_row[] = (string)$rows[$key]->$name;

				//CMSのログインID設定
				}else if($name == "cms_id") {
					$csv_row[] = (string)$accountRow[0]->login_id;

				//CMSのログインパスワード設定
				}else if($name == "cms_password") {
					$csv_row[] = (string)$accountRow[0]->password;

				//最終更新日を設定
				}else if($name == "release_date") {

					//まだ作成していない
					if($assCompHpRow == null) {
						$csv_row[] = '';
						continue;
					}
                    $where = [['hp_id', $assCompHpRow->current_hp_id], ['public_flg', 1]];
					//print($select->__toString());
					$pubRow = $hpObj->fetchRow($where, ['DESC' => 'published_at']);
					if($pubRow == null) {
						$csv_row[] = '';
					}else{
						$csv_row[] = $pubRow->published_at;
					}

				//最終更新停止日を設定
				}else if($name == "published_stop_date") {

					$delRow = $logDelObj->getLastDeleteForComapnyId($val['id']);
					if($delRow == null) {
						$csv_row[] = '';
					}else{
						$csv_row[] = $delRow->datetime;
					}

				}else{
					$csv_row[] = (string)$val[$name];
				}


			}
			fputcsv($stream, $csv_row);
		}
		fclose($stream);
		exit;
	}

	/**
	 * PDF出力用
	 */
    public function pdf(Request $request)
    {

		if(!$request->has("id") || $request->id == "" || !is_numeric($request->id)) {
			throw new Exception("No Company	 ID. ");
			exit;
		}

		//パラメータ取得
		$params = $request->all();

		//オブジェクト取得
		$companyObj = App::make(CompanyRepositoryInterface::class);

		//契約店情報の取得
		$company_row = $companyObj->getDataForId($params['id']);
		if($company_row == null) {
			throw new Exception("No Company Data. ");
			exit;
		}
		$this->view->company = $company_row;
		$this->view->mapOption	= $this->isMapOption($company_row->cms_plan,$company_row->map_start_date,$company_row->map_end_date);
        //CMSログインアカウント情報の取得
        $companyAccountObj = App::make(CompanyAccountRepositoryInterface::class);
		$rowsObj = $company_row->companyAccount()->first();
		$this->view->account = $rowsObj;


		$tagObj = App::make(TagRepositoryInterface::class);
		$row = $company_row->tag()->first();
		$this->view->google = $row;

		$row = $company_row->secondEstate()->first();
            /*
				利用する表示
				　　開始日が設定（過去・未来問わず）and 利用停止日がブランク
				
				　　利用しない表示
				　　　　開始日がブランク or 利用停止日が設定（過去・未来問わず）
             */
		$isUse = !is_null($row) && ($row->start_date) && !($row->end_date) ? true : false;
		$secondEstate = new stdClass;
		if ($isUse){
			$secondEstate->isUse = '利用する'; 
			$prefCodes = json_decode($row->area_search_filter)->area_1;
			$prefs = PrefCodeList::getInstance()->pick($prefCodes);
			$secondEstate->area = implode(' ', $prefs); 
		} else {
			$secondEstate->isUse = '利用しない'; 
		    $secondEstate->area = '－'; 
		}
		
		$this->view->secondEstate = $secondEstate;

        $rowOriginal = $company_row->originalSetting()->first();
        $datetime = new DateTime();
        $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $today = $datetime->format('Ymd');

        $isUseOriginal = !is_null($rowOriginal) && ($rowOriginal->start_date) && (!$rowOriginal->end_date || strtotime($rowOriginal->end_date) > strtotime($today)) ? true : false;
        $originalSetting = new stdClass();
        $originalSetting->plan = true;
        if ($isUseOriginal) {
            $originalSetting->isUse = '利用する';
        } else {
            $originalSetting->isUse = '利用しない';
        }

        $this->view->originalSetting = $originalSetting;

		//何使おうかしら
		//http://codezine.jp/article/detail/7141

		// KaiinSummaryApi用パラメータ作成
		$kaiin_no = $company_row['member_no'];
		$apiParam = new Kaiin\KaiinSummary\KaiinSummaryParams();
		$apiParam->setKaiinNo($kaiin_no);
		// 会員APIに接続して会員情報を取得
		$apiObj = new Kaiin\KaiinSummary\GetKaiinSummary();
		$kaiinDetail = (object) $apiObj->get($apiParam, '会員概要取得');
		
		if (isset($kaiinDetail->mainTantoCd)) {
			// TantoApi用パラメータ作成
			$tantoApiParam = new Kaiin\Tanto\TantoParams();
			$tantoApiParam->setTantoCd($kaiinDetail->mainTantoCd);
			//会員APIに接続して担当者情報を取得
			$tantoapiObj = new Kaiin\Tanto\GetTanto();
			$tantouInfo = (object) $tantoapiObj->get($tantoApiParam, '担当者取得');
			if (isset($tantouInfo->tantoShozoku["mShozokuKaName"])) {
				$shozokuka = $tantouInfo->tantoShozoku["mShozokuKaName"];
				$this->view->shozokuka = $shozokuka;
			} else {
				throw new Exception("No Shozokuka Data. ");
				exit;
			}
		}else{
			throw new Exception("No KaiinSummary Data. ");
			exit;	
        }
        
        $this->view->listCmsPlan = new CmsPlan();
        $html = view("admin::company.pdf")->render();
        $config = [
            'mode' => 'ja+aCJK', //モード default ''
			'format' => 'A4', //用紙サイズ default''
			'default_font_size' => 8, //フォントサイズ default 0
			'default_font' => 'メイリオ', //フォントファミリー
			'margin_left' => 10, //左マージン
			'margin_right' => 10, //右マージン
			'margin_top' => 5, //トップマージン
			'margin_bottom' => 5, //ボトムマージン
			'margin_header' => 0, //ヘッダーマージン
			'margin_footer' => 0, //フッターマージン
			'orientation' => '', //L-landscape,P-portrait
            'tempDir' => base_path('storage/app/mpdf'),
        ];
		$mpdf = new Mpdf($config);
		$mpdf->mirrorMargins = 0;
		$mpdf->WriteHTML($html);
		$data  = $mpdf->Output("", "S");

		$ua = $_SERVER['HTTP_USER_AGENT'];
		$file_name = $company_row->company_name .'_利用開始通知書.pdf';
        $file_name_encoded = $file_name;

		if (strstr($ua, 'Trident') || strstr($ua, 'MSIE')) {
            $file_name_encoded = mb_convert_encoding($file_name, "SJIS-win","UTF-8");
        }

		// PDFを出力します
		header('Content-Type: application/pdf');
		// downloaded.pdf という名前で保存させます
		header('Content-Disposition: attachment; filename="'. $file_name_encoded .'"; filename*=UTF-8\'ja\'' . rawurlencode($file_name));
		echo $data;
		exit;
	}

    /**
     * login agency show prewiew
     */
    public function loginForword($companyId){
        getUser()->setAgency(new Agency);
        if(!getUser()->getAgency()->getAdminProfile()){
            getUser()->getAgency()->setAdminProfile(getUser()->getProfile());
        }
        $companyObj = App::make(CompanyRepositoryInterface::class);
        $row = $companyObj->getDataForId($companyId);
        getUser()->getAgency()->loginAgency($row->member_no);
    }
    
    /**
     * get params preview
     */
    public function getParamsPreview(Request $request) {

        // $this->_helper->layout->disableLayout();
        // $this->_helper->viewRenderer->setNoRender();
        $params = $request->all();
        $companyId = $request->company_id;
        $this->loginForword($companyId);
        $row = $this->_checkCompanyTOP($companyId);
        // Check expired contact or creatorHp exist
        if (!$row->isAvailable() || !$row->getCurrentCreatorHp()) {
            return;
        }
        $hp = $row->getCurrentCreatorHp();
        $data = array();
        $topPage = App::make(HpPageRepositoryInterface::class)->getTopPageData($hp->id);
        if (isset($params['parts'])) {
            foreach ($params['parts'] as $id=>$value) {
                if ($value['display_flg'] == '1') {
                    $row = App::make(HpMainPartsRepositoryInterface::class)->fetchRow(array(['id'=>$id]));
                    $value['special_id'] = $row->attr_1;
                    $data['koma'][$id] = $value;
                }
            }
        } else {
            $komaParts = App::make(HpMainPartsRepositoryInterface::class)->getPartsByType($topPage->id, HpMainPartsRepository::PARTS_ESTATE_KOMA);
            if ($komaParts) {
                foreach ($komaParts as $koma) {
                    $data['koma'][$koma->id]= array(
                        'display_flg' => '1',
                        'special_id' =>  $koma->attr_1,
                        'pc_columns' => $koma->attr_4,
                        'pc_columns_disable' => $koma->attr_5,
                        'pc_rows' => $koma->attr_6,
                        'pc_rows_disable' => $koma->attr_7,
                        'sp_columns' => $koma->attr_8,
                        'sp_columns_disable' => $koma->attr_9,
                        'sp_rows' => $koma->attr_10,
                        'sp_rows_disable' => $koma->attr_11,
                        'sort_option' => $koma->attr_3,
                    );
                }
            }
        }
        if (isset($params['settings'])) {
            foreach ($params['settings'] as $settings) {
                $data['notifications'][] = $settings;   
            }
        }
        $data['page_id'] = $topPage->id;
        if (isset($params['navigation'])) {
            $data['navigation'] = $params['navigation']['global_navigation'];
        }
        $data['token'] = getUser()->getAgency()->regenerateCsrfToken();
        return $this->success($data); 
    }

    /**
     * 公開処理実行中判断：ajax
     */
    public function publishLock(Request $request) {
        if(!$request->company_id || $request->company_id == "" || !is_numeric($request->company_id)) {
            throw new Exception("No Company ID. ");
            exit;
        }
        if (!$request->isMethod('post')) {
            throw new Exception("不正なアクセスです。");
        }
        header("Content-Type: application/json; charset=UTF-8");
        $tableCompany = App::make(CompanyRepositoryInterface::class);

        // 公開処理チェック
        $publishConfig = getConfigs('publish')->publish;
        $getLockKey = sprintf("%s_%d", $publishConfig->lock_key_prefix, $this->companyRow->id);
        try {
            // NHP-5403 公開処理中の契約情報更新の可否判断対策：排他チェック
            $stmt = \DB::select(sprintf("SELECT IS_FREE_LOCK('%s') AS LOCK_RES", $getLockKey));
            $isFreeLock = $stmt->fetch();
            echo json_encode($isFreeLock);
            exit;
        } catch (Exception $e) {
            throw new Exception('対象の会員が公開処理中か特定できませんでした。');
        }
    }

    private function isMapOption($plan,$mapStartDate,$mapEndDate){
		if($plan === config('constants.cms_plan.CMS_PLAN_ADVANCE')){
			return true;
		}elseif($mapStartDate && !($mapEndDate)){
			return true;
		}
		return false;
	}
}