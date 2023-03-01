<?php
namespace App\Repositories\EstateContactCount;

use App\Repositories\CountRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\Company\CompanyRepositoryInterface;


class EstateContactCountRepository extends CountRepository implements EstateContactCountRepositoryInterface
{
    protected $_name = 'estate_contact_count';

    public function getModel()
    {
        return \App\Models\EstateContactCount::class;
    }

    public function saveCount($company,$apiData, $estateData) {

        $dataModel = $estateData['bukken']['data_model'];
        $dispModel = $estateData['bukken']['display_model'];

        $company_id    = $company['id'];
        $pageTypeCode  = $apiData['page_type_code'];
        $estateNumber  = $dispModel['bukken_no'];
        $device        = ($apiData['device'] == 'pc') ? 1 : 2;
        $userIp        = empty($apiData['user_ip']) ? null : $apiData['user_ip'] ;
        $userAgent     = $apiData['useragent'];
        $specialId     = empty($apiData['special_id']) ? null : $apiData['special_id'] ;
        $recommendFlg  = $apiData['recommend_flg'];
        $from_searchmap= ($apiData['from_searchmap']) ? 1 : 0;
        // 4293: Add FDP contact log
        $peripheralFlg = $apiData['peripheral_flg'];

		$bukken_id     = $dispModel['id'];
		$version_no    = empty($dataModel['version_no']) ? null : $dataModel['version_no'];

        // 2次広告自動公開物件フラグ
        $secondEstateFlg = ($estateData['second_estate_flg']) ? 1 : 0;

        $data = array(
            'company_id'           => $company_id,      // サイトを契約している会員のカンパニーID
            'page_type_code'       => $pageTypeCode,    // ページタイプコード
            'estate_number'        => $estateNumber,    // 物件番号
            'second_estate_flg'    => $secondEstateFlg, // 2次広告自動公開のフラグ
            'special_id'           => $specialId,       // 特集ID
            'recommend_flg'        => $recommendFlg,    // おすすめフラグ
            'from_searchmap'       => $from_searchmap,  // 地図検索遷移フラグ
            'user_ip'              => $userIp,          // ユーザーIP
            'user_agent'           => $userAgent,       // ユーザーエージェント
            'device'               => $device,          // デバイス
            'peripheral_flg'       => $peripheralFlg,   // 4293: Add FDP contact log
            'recieve_date'         => date('Y-m-d H:i:s'),
            'bukken_id'            => $bukken_id,       // 物件ID
            'version_no'           => $version_no,      // 物件バージョン番号
       );
       if ($this->isValidate($data, $apiData, 'estate_contact_count')) {
          return $this->create($data);
       } else {
       	  $companyObj = \App::make(CompanyRepositoryInterface::class)->find($company_id);
          $this->sendMail($companyObj);
       }
    }

    public function getData() {
        $select = $this->model->select();
        $select->from($this->_name);

        $data = $this->fetchAll($select);

        return $data;
    }
    public function getPerDayData($day='-1') {
        $select = $this->model->select();
        $select->from($this->_name);
        $select->whereRaw("DATE_FORMAT(recieve_date, '%Y-%m-%d') = ADDDATE(CURRENT_DATE(), " . $day. ")");

        $data = $this->fetchAll($select);

        return $data;
    }

}