<?php
namespace App\Repositories\Conversion;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class ConversionRepository extends BaseRepository implements ConversionRepositoryInterface
{
    protected $_name = 'conversion';

    public function getModel()
    {
        return \App\Models\Conversion::class;
    }

    const CONVERSIONTYPE_TELTAP = 1;

    public function saveTeltap($pageUrl,$device,$userIp,$userAgent,$companyId) {

        $conversionType = self::CONVERSIONTYPE_TELTAP;
        $pageUrl        = empty($pageUrl) ? null : $pageUrl ;
        $device         = ($device == 'pc') ? 1 : 2;
        $userIp         = empty($userIp) ? null : $userIp ;
        $userAgent      = empty($userAgent) ? null : $userAgent ;

        $data = array(
            'conversion_type'      => $conversionType,
            'page_url'             => $pageUrl,
            'device'               => $device,
            'user_ip'              => $userIp,          // ユーザーIP
            'user_agent'           => $userAgent,       // ユーザーエージェント
            'recieve_date'         => date('Y-m-d H:i:s'),
            'company_id'           => $companyId,
       );

       return $this->create($data);
    }


    /** 対象日の電話番号タップコンバージョン情報を取得する
     * @param $recieveDate
     * @return App\Collections\CustomCollection
     */
    public function getTeltap($recieveDate) {

        $this->setAutoLogicalDelete(false);
        $select = $this->model->withoutGlobalScopes();

        $cols = array(
            'cvr.conversion_type as conversion_type ',
            'cvr.page_url as page_url',
            'cvr.device as device',
            'cvr.user_ip as user_ip',
            'cvr.user_agent as user_agent',
            'cvr.company_id as company_id',
            'cvr.recieve_date as recieve_date',
            'com.member_no as member_no',
        );

        $select->selectRaw(implode(',', $cols));

        $conversionType = self::CONVERSIONTYPE_TELTAP;
        $recieveDate = $recieveDate->format('Y-m-d');

        $select->from($this->_name . ' as cvr');
        $select->leftJoin('company as com', 'com.id', '=', 'cvr.company_id');
        $select->whereRaw('DATE_FORMAT(cvr.recieve_date, "%Y-%m-%d") = "' . $recieveDate . '"');
        $select->where('cvr.conversion_type', $conversionType);
        $select->where('com.delete_flg', 0);
        $select->where('cvr.delete_flg', 0);
        $rowSet = $this->fetchAll($select);

        return $rowSet;
    }
}