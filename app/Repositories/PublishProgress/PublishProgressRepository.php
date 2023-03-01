<?php
namespace App\Repositories\PublishProgress;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Library\Custom\Mail;

class PublishProgressRepository extends BaseRepository implements PublishProgressRepositoryInterface
{
    protected $_name = 'publish_progress';

    protected $_auto_logical_delete = false;

    public static $batchLoginId = 'batch';

    private $_mailFrom;
    private $_mailTos;
    private $_envJp;

    public function getModel()
    {
        return \App\Models\PublishProgress::class;
    }

    /**
     * 新規レコード作成(progress)を更新する
     * @param int $pr_data ( publish_type / hp_id / company_id)
     */
    public function createProgress($pr_data, $compnay_name=null) {

        $publishConfig = getConfigs('publish')->publish;

        $this->_mailFrom = $publishConfig->mail_from;
        $this->_mailTos = $publishConfig->mail_tos;
        $this->_envJp = $publishConfig->env_jp;

        if($pr_data['login_id'] != self::$batchLoginId) {
            $pr_data['session_id'] = session_id();
        }
        $pr_data['process_id'] = getmypid();
        $pr_data['environment'] = \App::environment();
        $pr_data['hostname'] = gethostname();

        $progress_id = $this->create($pr_data)->id;

        $this->updateProgress($progress_id, '開始');

        if(isset($pr_data['success_notify']) && $pr_data['success_notify'] == 1) {
            // 開始メールを送信する
			$commonSubject = '【hpadvance】'. $compnay_name .' ';
			if(\App::environment() != 'production') {
				$subject = $this->_envJp . $commonSubject;
			} else {
				$subject = $commonSubject;
			}
			$subject.= '公開処理を開始しました';
			$body = '';
            try {
                // $mail = new Zend_Mail('ISO-2022-JP');
                $mail = new Mail();
                // $mail->setBodyText(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"), null, Zend_Mime::ENCODING_7BIT);
                $mail->setBody(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"));
                $mail->setFrom($this->_mailFrom,'');
                $mail->addTo($this->getMailTos());
                // $mail->setSubject(mb_convert_encoding($subject, "ISO-2022-JP", "UTF-8"));
                $mail->setSubject($subject);
                $mail->send();
            } catch (\Exception $e) {}
        }

        return $progress_id;
    }

    /**
     * 現在の進捗情報(progress)を更新する
     * @param int $id
     * @param string $newProgress
     */
    public function updateProgress($id, $new_progress) {
        $date = Carbon::now();
        $add_progress = sprintf("[%s] %s\n", $date->format('Y-m-d H:i:s'), $new_progress);
        $sql = sprintf("UPDATE %s SET progress = CONCAT(IFNULL(`progress`, ''), '%s') WHERE id = %s", $this->_name, $add_progress, $id);
        \DB::statement($sql);
    }

    /**
     * pages.txt を unserializeし、ページ数を設定する
     * @param int $id
     * @param int $hp_id
     * @param int $publish_type
     */
    public function countPages($id, $hp_id = null, $publish_type = null) {

        if(is_null($hp_id) || is_null($publish_type)) {
            $res = $this->find($id);
            $hp_id = $res['hp_id'];
            $publish_type = $res['publish_type'];
        }
        $public_type_path = \Library\Custom\Publish\Ftp::getPublishName($publish_type);

        $pages_file = sprintf("%s/data/html/temp/%d/files/%s/setting/pages.txt", storage_path(), $hp_id, $public_type_path);
		// error_log("pages.txt : ". $pages_file);

        $num_of_pages = 0;

        // 通常ページ数:serializeファイルから取得
        $num_of_pages += count(unserialize(file_get_contents($pages_file)));

        // 特集数
        $o1 = null;
        $cmd = "find " . sprintf("%s/data/html/temp/%d/files/%s/view/pc", storage_path(), $hp_id, $public_type_path) . " -type d -name 'sp-*'";
		exec($cmd, $o1, $o2);
		$num_of_pages += count($o1);

        $this->update($id, array('num_of_pages' => $num_of_pages));
    }

    /**
     * 処理結果(status)を設定する
     * @param int $id
     * @param boolean $status
     * @param string $error_msg
     */
    public function publishFinish($id, $status, $error_msg=null) {
        $result = array(
            'status' => $status,
            'finish_time' => Carbon::now()->format('Y-m-d H:i:s')
        );
        $progress = '';
        if($status == 1) {
            // 正常時はprogressを終了にする
            $progress = '終了';
        } else {
            $progress = '異常終了';
            $result['exception_msg'] = $error_msg;
        }
        $this->updateProgress($id, $progress);
        $this->update($id, $result);
	}

    /**
     * メール未通知(reported_flg = 0)の一覧を取得する
     */
    public function getUnNotifyProgress() {
        // 本テーブル(publish_progress)と companyテーブルを結合する
        $s = $this->model->selectRaw('pp.*, cp.member_no, cp.domain, cp.contract_type, cp.member_name, cp.company_name');
        $s->from($this->_name.' as pp');
        $s->leftJoin('company as cp', function($join){
            $join->on('pp.company_id', 'cp.id');
        });
        $s->where('pp.reported_flg', 0);
        // $s->where('pp.delete_flg', 0);
        $row = $s->withoutGlobalScopes()->get();

        return $row;
    }

    /**
     * 指定IDのレコードを通知済み(reported_flg=1)に更新する
     * @param int $id
     */
    public function setReported($id) {
        $reported = array(
            'reported_flg' => 1
        );
        $this->update($id, $reported);
    }


    /**
     * メールの送信元(From)を取得する
     */
    public function getMailFrom() {
        return $this->_mailFrom;
    }

    /**
     * メールの送信先を取得する
     */
    public function getMailTos() {
        $mailTos = $this->_mailTos;
        return $mailTos;
    }

    public function getDataPublishForCompany() 
    {
        $select = $this->model->select("publish_progress.*",
        'c.member_no',
        'c.domain',
        'c.contract_type',
        'c.member_name',
        'c.company_name');
        $select->Leftjoin("company as c", "publish_progress.company_id", "=", "c.id");
        $select->orderBy('publish_progress.id','DESC');
        return $select;
    }
}