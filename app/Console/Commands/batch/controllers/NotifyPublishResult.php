<?php
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\PublishProgress\PublishProgressRepositoryInterface;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use Library\Custom\Mail;
use Laminas\Mime\Mime;
use Illuminate\Support\Facades\DB;

class NotifyPublishResult extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'command:batch-notify-publish-result {env?} {app?} {controller?}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command notify publish result';
 
	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
    
    private $_mailFrom;
    private $_mailTos;
    private $_envJp;

	public function handle()
	{
		try {
			$arguments = $this->arguments();
			BatchAbstract::validParamater($arguments, $this);
 
			$this->_info->info('/////////////// START ///////////////');
            set_time_limit(0);

            // 設定ファイル(publish.ini)の読み込み
            $publishConfig = getConfigs('publish')->publish;

            $timeout = 20;
            if(isset($publishConfig->timeout) && is_numeric($publishConfig->timeout)) {
                $timeout = $publishConfig->timeout;
            }
            $abnormalErrTs = time() - $timeout * 60;

            $this->_mailFrom = $publishConfig->mail_from;
            $this->_mailTos = $publishConfig->mail_tos;
            $this->_envJp = $publishConfig->env_jp;

            $progressTable = App::make(PublishProgressRepositoryInterface::class);
            // $adapter = $progressTable->getAdapter();

            // 排他制御
            $exclusive_flg = true;
            try {
                $getLockKey = $publishConfig->notify_lock_key;

                // ロック中確認
                // $stmt = $adapter->query(sprintf("SELECT IS_FREE_LOCK('%s') AS LOCK_RES", $getLockKey));
                // $row = $stmt->fetch();
                $row = DB::select(sprintf("SELECT IS_FREE_LOCK('%s') AS LOCK_RES", $getLockKey));
                if(!$row[0]->LOCK_RES) {
                    throw new \Exception("他プロセスがロック中のため処理を中断しました。");
                }
                // ロック実行
                // $stmt = $adapter->query(sprintf("SELECT GET_LOCK('%s', %d) AS LOCK_RES", $getLockKey, $publishConfig->lock_wait));
                // $row = $stmt->fetch();
                $row = DB::select(sprintf("SELECT GET_LOCK('%s', %d) AS LOCK_RES", $getLockKey, $publishConfig->lock_wait));
                if(!$row[0]->LOCK_RES) { // ロックに失敗(10s)
                    throw new \Exception("ロック処理に失敗しました。");
                }
            } catch(\Exception $e) {
                $this->_error->error($e);
                $exclusive_flg = false;
            }

            if($exclusive_flg) {
                // メール未送信の一覧を取得する
                $pps = $progressTable->getUnNotifyProgress();

                foreach($pps as $pp) {
                    // statusが終了でない場合の特殊処理
                    if(is_null($pp->status)) {
                        // 処理中の可能性あり...
                        if(strtotime($pp->update_date) >= $abnormalErrTs) {
                            continue;
                        }

                        try {
                            // エラーに書き換え、DB更新
                            $pp->status = 0;
                            $pp->exception_msg = '異常終了(TimeOut)';
                            $progressTable->publishFinish($pp->id, $pp->status, $pp->exception_msg);
                        } catch(\Exception $e) {
                            // 更新失敗で次回繰り越し
                            continue;
                        }
                    }
                    $this->_info->info("--- ID(" . $pp->id .") com_id:". $pp->company_id. " /  hp_id:" . $pp->hp_id . " 公開結果通知 ---");
                    // メール送信
                    if($this->_sendNotify($progressTable, $pp)) {
                        $progressTable->setReported($pp->id);
                    }
                    $this->_info->info("--- ID(" . $pp->id .") 完了 ---");
                }

                // Lock解放
                // $stmt = $adapter->query(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));
                // $row = $stmt->fetch();
                $row = DB::select(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));
            }
            $this->_info->info('//////////////// END ////////////////');
        }catch (\Exception $e) {
            $this->_error->error($e);
        }
    }

    private function _sendNotify($progressTable, $pp) {
        // 成功かつメール通知不要なら終了
        if($pp->status == 1 && $pp->success_notify == 0) {
            $this->_info->info("送信不要");
            // $progressTable->setReported($pp->id);
            return true;
        }

        if($pp->status == 1) {
            $statusStr = '成功';
            $this->_info->info("成功メール送信");
        } else {
            $statusStr = '失敗';
            $this->_info->info("失敗メール送信");
        }

        // 操作環境
        $envStr = "(Unknown)";
        if(!empty($this->_envJp)) {
            $envStr = $this->_envJp;
        }
        // $this->_info->info($envStr);
        // 公開先
        $ptypeStr = "";
        switch($pp->publish_type) {
            case 1:
                $ptypeStr = "本番サイト";
                break;
            case 2:
                $ptypeStr = "テストサイト";
                break;
            case 3:
                $ptypeStr = "代行テストサイト";
                break;
            default:
                $ptypeStr = "(Unknown)";
                break;
        }

        $subject = sprintf("【%s】%s/%s/%s/公開処理通知", $statusStr, $envStr, $ptypeStr, $pp->member_no);
        $body = $this->_makeNotifyBody($pp);
        // $this->_info->info($subject);
        // $this->_info->info($body);

        try {
            // $mail = new Mail('ISO-2022-JP');
            $mail = new Mail();
            // $mail->setBodyText(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"), null, Mime::ENCODING_7BIT);
            $mail->setBody(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"));
            $mail->setFrom($this->_mailFrom,'');
            $mail->addTo($this->_mailTos);
            // $mail->setSubject(mb_convert_encoding($subject, "ISO-2022-JP", "UTF-8"));
            $mail->setSubject($subject);
            $mail->send();
        } catch(\Exception $e) {
            $this->_error->error($e);
            return false;
        } 
        return true;
    }

    private function _makeNotifyBody($pp) {
        switch($pp->status) {
            case 1: // 成功
                $mailBody = $this->makeSuccessBody($pp);
                break;
            case 0: // 失敗
            default:
                $mailBody = $this->makeFailedBody($pp);
                break;
        }
        return $mailBody;
    }

    private function makeSuccessBody($pp) {
        $siteMapUrl = $this->_getSiteMapUrl($pp);
        $body =<<<BODY
            公開処理が完了しました。（${siteMapUrl}）
            BODY;
        return $body;
    }

    private function makeFailedBody($pp) {
        $siteMapUrl = $this->_getSiteMapUrl($pp);
        $exceptionMsg = $pp->exception_msg;
        $memberNo = $pp->member_no;
        $companyName = $pp->company_name;
        $memberName = $pp->member_name;
        $body =<<<BODY
            ${exceptionMsg}

            会員NO：${memberNo}

            社名：${companyName}

            サイトマップ：${siteMapUrl}

            BODY;
        return $body;
    }

    private function _getSiteMapUrl($pp) {
        $domain = $pp->domain ;
        if($pp->publish_type == 1) {
           $url = sprintf("https://www.%s/sitemap/", $domain);
        } else {
           // Config参照
            if($pp->contract_type == config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') ) {
                $config=getConfigs('sales_demo');
                $domain  = "{$pp->member_no}.{$config->demo->domain}";
            }
            if($pp->publish_type == 2) {
                $url = sprintf("http://test.%s/sitemap/", $domain);
            } else {
                $url = sprintf("http://substitute.%s/sitemap/", $domain);
            }
        }
        return $url;
    }
}

// not finished  //

// docker exec -it servi_80 bash 
// php artisan command:batch-notify-publish-result development app NotifyPublishResult>> /var/www/html/storage/logs/NotifyPublishResult.log 2>&1
