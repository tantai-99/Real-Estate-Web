<?php
namespace Library\Custom\View\Helper;

use Library\Custom\Publish\Render;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * サイト内リンクのURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpLinkHouse extends  HelperAbstract
{
    /**
     * @var App\Models\Company
     */
    private static $_company;

    /**
     * @var int
     */
    private static $_publish_type;

    private static $_isPreview;

    public static function setCompany($company)
    {
        self::$_company = $company;
    }

    public static function setPublishType($type)
    {
        self::$_publish_type = $type;
    }

    public static function setPreview($isPreview) {

        self::$_isPreview = $isPreview;
    }

    public function hpLinkHouse($path)
    {
        if(is_string($path) && is_array($jsonData = json_decode(html_entity_decode($path), true)) && (json_last_error() == JSON_ERROR_NONE)) {
            if (self::$_isPreview) {
                $path = $jsonData['url'];
            } else {
                return '<?php if(!$this->viewHelper) {$this->viewHelper = new ViewHelper($this->_view);} echo $this->viewHelper->hpLinkHouse(\''.html_entity_decode($path).'\');?>';
            }
        }
        $domain  = Render\AbstractRender::www($this->_view->mode).Render\Content::prefix($this->_view->mode).$this->_view->company->domain;
        $baseUrl = Render\AbstractRender::protocol($this->_view->mode).$domain;
        return $baseUrl.$path;
    }
}