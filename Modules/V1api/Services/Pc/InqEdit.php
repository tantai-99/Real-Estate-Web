<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class InqEdit extends Services\AbstractElementService
{
    public $head;
	public $content;
    public $header;
    public $isFDP;

    const H1_TEXT = 'お問い合わせページ。';

    const KASI_KYOJUU = 'kasi-kyojuu';
    const KASI_JIGYOU = 'kasi-jigyou';
    const URI_KYOJUU  = 'uri-kyojuu';
    const URI_JIGYOU  = 'uri-jigyou';
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        $this->head = $this->head($params, $settings);
		$this->content = $this->content($params, $settings, $datas);
        $this->header = $this->header();
        $this->isFDP = $this->isFDP($params, $settings);
	}
	
	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
	}

    private function head(
        Params $params,
        Settings $settings)
    {
        $pageSetting       = $settings->page;
        $head              = new Services\Head();

        $contactCt = $params->getContactCt();
        $sell_or_rent_ct_jp = $this->getSellOrRentCtJp($contactCt);
        $kyojuu_or_jigyou_ct_jp = $this->getKyojuuOrJgyouCtJp($contactCt);

        $head->title       = "お問い合わせ（{$sell_or_rent_ct_jp}{$kyojuu_or_jigyou_ct_jp}）｜{$pageSetting->getSiteName()}";
        $head->keywords    = "{$sell_or_rent_ct_jp}{$kyojuu_or_jigyou_ct_jp},問い合わせ,{$pageSetting->getKeyword()}";
        $head->description = "{$kyojuu_or_jigyou_ct_jp}{$sell_or_rent_ct_jp}物件のお問い合わせページ。{$pageSetting->getDescription()}";
        return $head->html();
    }

	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/inquiry/content.tpl");
		
        // 会社名
        $comName = $settings->page->getCompanyName();

        $pNames = $datas->getParamNames();
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        if (!empty($type_ct))
        {
            $shumoku    = $pNames->getShumokuCd();
            switch ($shumoku)
            {
            case Services\ServiceUtils::TYPE_CHINTAI:
                $mtype_ct = 'rent';
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
                $mtype_ct = 'office';
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                $mtype_ct = 'parking';
                break;
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
            case Services\ServiceUtils::TYPE_KASI_OTHER:
                $mtype_ct = 'others';
                break;
            case Services\ServiceUtils::TYPE_MANSION:
                $mtype_ct = 'mansion';
                break;
            case Services\ServiceUtils::TYPE_KODATE:
                $mtype_ct = 'house';
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
                $mtype_ct = 'land';
                break;
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $mtype_ct = 'business';
                break;
            default:
                throw new \Exception('Illegal Argument.');
                break;
            }
        } else {
            $mtype_ct = $params->getMTypeCt();
            switch ($mtype_ct)
            {
                case 'rent':
                    $shumoku = Services\ServiceUtils::TYPE_CHINTAI;
                    break;
                case 'office':
                    $shumoku = Services\ServiceUtils::TYPE_KASI_TENPO;
                    break;
                case 'parking':
                    $shumoku = Services\ServiceUtils::TYPE_PARKING;
                    break;
                case 'others':
                    $shumoku = Services\ServiceUtils::TYPE_KASI_OTHER;
                    break;
                case 'mansion':
                    $shumoku = Services\ServiceUtils::TYPE_MANSION;
                    break;
                case 'house':
                    $shumoku = Services\ServiceUtils::TYPE_KODATE;
                    break;
                case 'land':
                    $shumoku = Services\ServiceUtils::TYPE_URI_TOCHI;
                    break;
                case 'business':
                    $shumoku = Services\ServiceUtils::TYPE_URI_OTHER;
                    break;
                default:
                    throw new \Exception('Illegal Argument.');
                    break;
            }
        }
        
        // sort-table処理
		$sortElem = $this->getTemplateDoc("/sorttable_inq.tpl");
        $doc['table']->append($sortElem['table.'. $mtype_ct . ' tr']);

        // 問い合わせ物件リスト
        $bukkenList = $datas->getBukkenList();

        // 物件一覧の生成
        // 必要要素の初期化とテンプレ化
        $bukkenMaker = new Element\BukkenListInq();
        foreach ($bukkenList['bukkens'] as $bukken)
        {
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            $bukkenElem = $bukkenMaker->createElementHachi($shumoku, $dispModel, $dataModel, $params);
            $doc['table']->append($bukkenElem);
        }

        return $doc->html();
    }

    protected function header (){

        $text = static::H1_TEXT;
        return "<h1 class=\"tx-explain\">{$text}</h1>";
    }

    /**
     * 賃貸 or 売買
     *
     * @param $contactCt
     * @return string
     */
    protected function getSellOrRentCtJp($contactCt) {

        switch ($contactCt) {
            case self::KASI_KYOJUU:
            case self::KASI_JIGYOU:
                return '賃貸';
            case self::URI_KYOJUU:
            case self::URI_JIGYOU:
                return '売買';
            default:
                return '';
        }
    }

    /**
     * 居住用 or 事業用
     *
     * @param $contactCt
     * @return string
     */
    protected function getKyojuuOrJgyouCtJp($contactCt) {

        switch ($contactCt) {
            case self::KASI_KYOJUU:
            case self::URI_KYOJUU:
                return '居住用';
            case self::KASI_JIGYOU:
            case self::URI_JIGYOU:
                return '事業用';
            default:
                return '';
        }
    }

    private function isFDP(
        Params $params,
        Settings $settings)
    {
        if (Services\ServiceUtils::isFDP($settings->page)) {
            return true;
        }
        return false;
    }
}