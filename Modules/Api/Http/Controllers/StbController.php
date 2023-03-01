<?php
namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StbController extends Controller {

	/**
	 * 契約担当者IDから担当者を渡すスタブ
	 * スタブ用のURL：/api/stb/get-kokyaku-kanri-keiyaku-kaiin-no
	 */
	public function getKokyakuKanriKeiyakuKaiinNo() {
		$moto_data = 
				'{
				  "model": {
				    "kaiinNo": "sample string 1",
				    "riyoKbn": "sample string 2",
				    "riyoKbnName": "sample string 3",
				    "mailBoxAddress": "sample string 4",
				    "riyoStartShinseiDate": "sample string 5",
				    "riyoStartDate": "sample string 6",
				    "keiyakuTantoCd": "sample string 7",
				    "keiyakuTantoName": "sample string 8",
				    "riyoStopShinseiDate": "sample string 9",
				    "riyoStopDate": "sample string 10",
				    "kaiyakuTantoCd": "sample string 11",
				    "kaiyakuTantoName": "sample string 12",
				    "kaiyakuRiyu": "sample string 13",
				    "biko5": "sample string 14",
				    "biko6": "sample string 15",
				    "createDate": "sample string 16",
				    "createTime": "sample string 17",
				    "creator": "sample string 18",
				    "updateDate": "sample string 19",
				    "updateTime": "sample string 20",
				    "updater": "sample string 21",
				    "thankyouRiyoShinseiDate": "sample string 22",
				    "thankyouRiyoStartDate": "sample string 23",
				    "firstRiyoStartDate": "sample string 24"
				  },
				  "errors": [
				    {
				      "message": "sample string 1",
				      "fields": [
				        "sample string 1",
				        "sample string 2"
				      ]
				    },
				    {
				      "message": "sample string 1",
				      "fields": [
				        "sample string 1",
				        "sample string 2"
				      ]
				    }
				  ],
				  "warnings": [
				    {
				      "message": "sample string 1",
				      "fields": [
				        "sample string 1",
				        "sample string 2"
				      ]
				    },
				    {
				      "message": "sample string 1",
				      "fields": [
				        "sample string 1",
				        "sample string 2"
				      ]
				    }
				  ]
				}';

		$arr = array();
		$arr = json_decode($moto_data, true);
		$arr['model']['mailBoxAddress'] = "aoi.tsukasa.dev2@gmail.com";
		$arr['model']['riyoStartDate']  = date("Ymd", mktime(0, 0, 0, date("m")  , date("d")+(mt_rand(-100,10)), date("Y")));
		$arr['model']['riyoStopDate']   = date("Ymd", mktime(0, 0, 0, date("m")  , date("d")+(mt_rand(0,130)), date("Y")));

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($arr);
        exit();
	}
}
