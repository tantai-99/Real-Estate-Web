<?php

/**
 * CMS操作ログ
 * 
 */

namespace Library\Custom\Logger;

use Illuminate\Support\Facades\App;
use App\Repositories\LogEdit\LogEditRepositoryInterface;

class CmsOperation extends AbstractLogger
{

    static protected $_instance;

    /** コンストラクタ
     *
     */
    public function __construct()
    {
        $this->cmsUser = getInstanceUser('cms');
    }


    // 代理ログ
    public function deputizeLog($editType)
    {
        if (!$this->cmsUser->isDeputize()) {
            return;
        }
        $this->_insertEditLog($editType);
    }


    public function creatorLog($editType, $pageId = null)
    {
        if (!$this->cmsUser->isCreator()) {
            return;
        }
        $this->_insertEditLog($editType, $pageId);
    }


    //CMS操作ログ
    public function cmsLog($editType)
    {

        $this->_insertEditLog($editType);
    }


    //CMS操作ログ（ページ編集）
    public function cmsLogPage($editType, $pageId = null)
    {

        $this->_insertEditLog($editType, $pageId);
    }


    //CMS操作ログ（物件設定）
    public function cmsLogEstate($editType, $class = null)
    {
        $attr = ['class' => $class];
        $attr = json_encode($attr);
        $this->_insertEditLog($editType, null, $attr);
    }


    //CMS操作ログ（2次広告設定）
    public function cmsLogSecondEstate($editType, $class = null)
    {
        $attr = ['class' => $class];
        $this->_insertEditLog($editType, null, json_encode($attr));
    }


    //CMS操作ログ（特集設定）
    public function cmsLogSpecial($editType, $filename = null, $title = null)
    {
        $attr = ['filename' => $filename, 'title' => $title];
        $this->_insertEditLog($editType, null, json_encode($attr, JSON_UNESCAPED_UNICODE));
    }

    //CMS操作ログ（画像/ファイル削除）
    public function cmsLogRemove($editType, $id = null)
    {
        $this->_insertEditLog($editType, null, $id);
    }

    // CMS操作ログ
    protected function _insertEditLog($editType, $pageId = null, $attr1 = null)
    {
        $staffId = null;
        // 代行ログイン
        if ($this->cmsUser->isAgent()) {
            $type = config('constants.log_type.LOGIN');
            $staffId = $this->cmsUser->getTantoCD();
        }
        // 代行作成
        else if ($this->cmsUser->isCreator()) {
            $type = config('constants.log_type.CREATE');
            $staffId = $this->cmsUser->getAdminProfile()->id;
        }
        // 会員ログ
        else {
            $type = config('constants.log_type.COMPANY');
        }

        $hpId = null;
        if ($hp = $this->cmsUser->getCurrentHp()) {
            $hpId = $hp->id;
        }

        // ユーザーIP
        $userIp    = $_SERVER["REMOTE_ADDR"];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipArray = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $userIp = $ipArray[0];
        }
        $companyId = $this->cmsUser->getProfile()->id;
        App::make(LogEditRepositoryInterface::class)->log(
            $type,
            $staffId,
            $companyId,
            $editType,
            $hpId,
            $pageId,
            $attr1,
            $userIp
        );
    }
}
