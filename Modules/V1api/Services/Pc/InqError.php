<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class InqError extends InqEdit
{
    public $head;
    public $header;

    const H1_TEXT = '入力エラーページ。';

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        $this->head = $this->head($params, $settings);
        $this->header = $this->header();
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

        $head->title       = "入力エラー｜{$pageSetting->getSiteName()}";
        $head->keywords    = "入力エラー,{$pageSetting->getKeyword()}";
        $head->description = "物件のお問い合わせ入力エラーページ。{$pageSetting->getDescription()}";
        return $head->html();
    }
}