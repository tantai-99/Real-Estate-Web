<?php
namespace App\Repositories\ContactLog;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use function Symfony\Component\Translation\t;

class ContactLogRepository extends BaseRepository implements ContactLogRepositoryInterface
{
    protected $_name = 'contact_log';

    public function getModel()
    {
        return \App\Models\ContactLog::class;
    }

    public function saveLog($pageTypeCode, $mailTos, $subject, $body, $hpId, $company_id, $mail_id = NULL, $user_id = NULL, $hankyo_plus_use_flg = 0) {

        $data = array(
            'page_type_code'       => $pageTypeCode,
            'notification_to_1'    => (isset($mailTos[0])) ? $mailTos[0] : NULL,
            'notification_to_2'    => (isset($mailTos[1])) ? $mailTos[1] : NULL,
            'notification_to_3'    => (isset($mailTos[2])) ? $mailTos[2] : NULL,
            'notification_to_4'    => (isset($mailTos[3])) ? $mailTos[3] : NULL,
            'notification_to_5'    => (isset($mailTos[4])) ? $mailTos[4] : NULL,
            'notification_subject' => $subject,
            'body'                 => $body,
            'hp_id'                => $hpId,
            'company_id'           => $company_id,
            'mail_id'              => $mail_id,
            'user_id'              => $user_id,
            'hankyo_plus_use_flg'  => $hankyo_plus_use_flg,
        );

        return $this->create($data);
    }

    public function getContactLog($where) {
        return $this->model->whereRaw($where)->get();
    }
}