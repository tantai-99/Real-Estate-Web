<?php
namespace App\Repositories\ContactCount;

use App\Repositories\CountRepository;
use App\Repositories\Company\CompanyRepositoryInterface;
use Carbon\Carbon;

class ContactCountRepository extends CountRepository implements ContactCountRepositoryInterface
{
    protected $_name = 'contact_count';

    public function getModel()
    {
        return \App\Models\ContactCount::class;
    }

    public function saveCount($pageTypeCode,$company_id,$apiData) {
        $device        = ($apiData['device'] == 'pc') ? 1 : 2;
        $userIp        = empty($apiData['user_ip']) ? null : $apiData['user_ip'] ;
        $userAgent     = $apiData['useragent'];
        $fontendDate = $apiData['fontend_send_date'] ? $apiData['fontend_send_date'] : null;
        $gmoDate = $apiData['gmo_send_date'] ? $apiData['gmo_send_date'] : null;
        $data = array(
            'page_type_code'       => $pageTypeCode,
            'company_id'           => $company_id,
            'device'               => $device,
            'user_ip'              => $userIp,          // ユーザーIP
            'user_agent'           => $userAgent,       // ユーザーエージェント
            'recieve_date'         => date('Y-m-d H:i:s'),
            'fontend_send_date'    => $fontendDate,
            'gmo_send_date'        => $gmoDate,
       );
       if ($this->isValidate($data, $apiData, 'contact_count')) {
          return $this->create($data);
       } else {
	      $companyObj = \App::make(CompanyRepositoryInterface::class)->find($company_id);
	      $this->sendMail($companyObj);
       }
    }

    public function getPerDayData($day='-1') {
        $select = $this->model->select();
        $select->whereRaw("DATE_FORMAT(recieve_date, '%Y-%m-%d') = ADDDATE(CURRENT_DATE(), " . $day. ")");//昨日の日付の分とってくる

        $data = $select->get();

        return $data;
    }

    /*
    * アクセス状況を取得
    *
    * @param int $company_id
    * @param string $baseMonth
    *
    */
    public function getAccess($company_id, $baseMonth="") {

        $nowDate  = new Carbon();
        if($baseMonth == "") $baseMonth = $nowDate->format('y-M');
        $baseMonth = (new Carbon($baseMonth))->format('y-M');
        $startDate = (new Carbon($baseMonth))->subMonthb(5)->format('y-M-01');
        $endDate   = (new Carbon($baseMonth))->format('y-M-d');

        $select = $this->model->select();
        $select->from($this->_name);
        $select->selectRaw('count(*) as cnt',"DATE_FORMAT(recieve_date, '%Y-%m') as recieve_month");
        $select->where("company_id", $company_id);
        $select->whereRaw("DATE_FORMAT(recieve_date, '%Y-%m-%d')", ">=", $startDate);
        $select->whereRaw("DATE_FORMAT(recieve_date, '%Y-%m-%d')", "<= ", $endDate);
        $select->where("delete_flg", 0);
        $select->groupRaw("DATE_FORMAT(recieve_date, '%Y-%m')");
        $select->orderRaw("DATE_FORMAT(recieve_date, '%Y-%m')");
        //var_dump($select->__toString());
        $rows =  $this->fetchAll($select);

        $row = array();
        foreach($rows as $key => $val) {
            $row[$val['recieve_month']] = $val['cnt'];
        }
        $average=0;
        for($i = 5; $i >= 0; $i--) {
            $month = (new Carbon($baseMonth))->subMonthb($i)->format('y-M');
            if(!isset($row[$month])) $row[$month] = 0;
            $average = $average+$row[$month];
        }
        $row["average"] = $average;
        return $row;

    }

}