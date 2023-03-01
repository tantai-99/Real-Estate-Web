<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class InqConfirm extends InqEdit
{
    public $head;
    public $header;
    public $isFDP;

    const H1_TEXT = '確認ページ。';

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        $this->head = $this->head($params, $settings);
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

        $head->title       = "確認（{$sell_or_rent_ct_jp}{$kyojuu_or_jigyou_ct_jp}）｜{$pageSetting->getSiteName()}";
        $head->keywords    = "{$sell_or_rent_ct_jp}{$kyojuu_or_jigyou_ct_jp},確認,{$pageSetting->getKeyword()}";
        $head->description = "{$kyojuu_or_jigyou_ct_jp}{$sell_or_rent_ct_jp}物件のお問い合わせ確認ページ。{$pageSetting->getDescription()}";
        return $head->html();
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