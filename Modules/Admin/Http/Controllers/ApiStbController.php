<?php
class Admin_ApiStbController extends Controller {

	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		$contextSwitch->setActionContext($this->getRequest()->getActionName(), array('json'))->initContext('json');
		$this->view->success = true;
		$this->view->data = new stdClass();
		$this->data = $this->view->data;
	}

	/**
	 * 会員Noから会員情報を取得するスタブ
	 */
	public function getCompanyForMembernoAction() {

		//会員Noがある場合
		$kaiNo = "000000000";
		if (!is_null($this->getParam("no")) && !empty($this->getParam("no"))) {
			$kaiNo = $this->getParam("no");
		}
		$stubMemNoLinkNoMap = array(
			'00000001'=>'00000001','00000002'=>'00000001','00000003'=>'00000001','00000004'=>'00000001','00000005'=>'00000001',
			'00000006'=>'00000002','00000007'=>'00000002','00000008'=>'00000002','00000009'=>'00000002','00000010'=>'00000002',
			'10000000'=>''        ,'10000001'=>null,
			'99020819'=>'078044'
		);
		$kaiLinkNo = "0000000"; 
		if( array_key_exists($kaiNo, $stubMemNoLinkNoMap)){
			$kaiLinkNo = $stubMemNoLinkNoMap[$kaiNo];
		}

		$moto_data = 
				'{
				"KAI_NO":"'.$kaiNo.'",
				"KAI_LINKNO":"'.$kaiLinkNo.'",
				"SYOUGO_SEIKI":"\u30a2\u30c3\u30c8\u30db\u30fc\u30e0(\u682a)\uff08\u30c7\u30e2\uff09\u8c4a\u7530\u3000\u529f",
				"SYOUGO_HAN":"\u30a2\u30c3\u30c8\u30db\u30fc\u30e0(\u682a)\uff08\u30c7\u30e2\uff09\u8c4a\u7530\u3000\u529f",
				"SYOUGO_HAN_RK":"\uff08\u30c7\u30e2\uff09\u8c4a\u7530\u3000\u529f",
				"SYOUGO_BUK":"\u30a2\u30c3\u30c8\u30db\u30fc\u30e0(\u682a)\uff08\u30c7\u30e2\uff09\u8c4a\u7530\u3000\u529f",
				"SYOUGO_BUK_RK":"\uff08\u30c7\u30e2\uff09\u8c4a\u7530\u3000\u529f",
				"SYOUGO_ATWEB":"\u30a2\u30c3\u30c8\u30db\u30fc\u30e0(\u682a)\uff08\u30c7\u30e2\uff09\u8c4a\u7530\u3000\u529f",
				"SYZ_CD1":"13111",
				"SYZ_CD2":"034008",
				"SYZ_IDO":"",
				"SYZ_KEIDO":"",
				"POST_NO":"1440056",
				"KEN_NM":"\u6771\u4eac\u90fd",
				"SYZ_NM1":"\u5927\u7530\u533a",
				"SYZ_NM2":"\u897f\u84b2\u7530\uff18\u4e01\u76ee",
				"BANCHI":"\uff12\uff0d\uff11\uff11",
				"TATEMONO_NM":"\uff21\uff30\uff2d\u84b2\u7530\u30d3\u30eb\uff14\uff26",
				"ENSENEKI_CD":"2331140",
				"KEN_ENSENEKI_CD":"2331140",
				"ENSEN_NM":"\u4eac\u6025\u672c\u7dda",
				"EKI_NM":"\u516d\u90f7\u571f\u624b",
				"TEL_DAIHYO":"03-3730-6456",
				"TEL_SEIYAKU":"03-3730-6456",
				"TEL_IPPAN":"03-3730-6456",
				"TEL_CHINTAI":"03-3730-6456",
				"FAX_DAIHYO":"03-3730-0899",
				"FAX_MAIL":"03-3730-0899",
				"FAX_ZUMEN":"03-3730-0899",
				"MAIL_DAIHYO":"aoi.tsukasa.dev1@gmail.com",
				"MAIL_TO_MAIL":"aoi.tsukasa.dev1@gmail.com",
				"MAIL_SEIYAKU":"aoi.tsukasa.dev1@gmail.com",
				"MAIL_FAXFK_FL":"",
				"URL_LONG":"",
				"MENKYO_NM":"\u6771\u4eac\u90fd\u77e5\u4e8b\u514d\u8a31\uff08\uff19\uff19\uff09\u7b2c\uff19\uff19\uff19\uff19\uff19\uff19\uff19\u53f7",
				"BU_CD":"015",
				"KA_CD":"112",
				"HAN_CD":"000",
				"TANTOSYA_CD":"01199",
				"FS_KAI_FL":"",
				"ONL_KAI_FL":"3",
				"CHINTAI_KAI_FL":"",
				"KYUMR_KAI_FL":"",
				"INTTOKU_KAI_FL":"",
				"IKKATU_KAI_FL":""
				}';

		//たまに空にしてそんな人居ないテスト
		//if(mt_rand(0, 10) < 3) $moto_data = array();

		$arr = array();
		if(count($moto_data) > 0) {
			$arr = json_decode($moto_data, true);

//			$arr['SYOUGO_SEIKI']  = $arr['SYOUGO_SEIKI'] ."_". mt_rand(1000,9999);
			$arr['SYOUGO_SEIKI']  = "テスト加盟店'(stub)_". mt_rand(1000,9999);
			$arr['SYOUGO_HAN']    = $arr['SYOUGO_HAN'] ."_". mt_rand(1000,9999);
			$arr['SYOUGO_HAN_RK'] = $arr['SYOUGO_HAN_RK'] ."_". mt_rand(1000,9999);
			$arr['SYOUGO_BUK']    = $arr['SYOUGO_BUK'] ."_". mt_rand(1000,9999);
			$arr['SYOUGO_BUK_RK'] = $arr['SYOUGO_BUK_RK'] ."_". mt_rand(1000,9999);
			$arr['SYOUGO_ATWEB']  = $arr['SYOUGO_ATWEB'] ."_". mt_rand(1000,9999);
			$arr['KEN_NM']        = $arr['KEN_NM'] ."_". mt_rand(1000,9999);
			$arr['SYZ_NM1']       = $arr['SYZ_NM1'] ."_". mt_rand(1000,9999);
			$arr['SYZ_NM2']       = $arr['SYZ_NM2'] ."_". mt_rand(1000,9999);
			$arr['BANCHI']        = $arr['BANCHI'] ."_". mt_rand(1000,9999);
			$arr['TATEMONO_NM']   = $arr['TATEMONO_NM'] ."_". mt_rand(1000,9999);
		}

		// 会員Noが数字でない場合は「空」でかえす。そんな人居ないテスト。
		if( !is_numeric($kaiNo) ){
			$arr = array();
		}

		$this->view->data = $arr;

        header("Content-Type: application/json; charset=utf-8");
        echo $this->getparam("callback") ."(". json_encode($arr) .")";
        exit();
	}

	/**
	 * 契約担当者IDから担当者を渡すスタブ
	 */
	public function getResponsibleCodeAction() {

		$moto_data = 
				'{
					"TANTOSYA_CD":"01199",
					"TANTOSYA_NM":"\u8c4a\u7530\u3000\u529f\u6b21\u90ce",
					"BU_CD":"015",
					"KA_CD":"112",
					"HAN_CD":"000",
					"EIGYOSHO_NM":"\uff29\uff34\u30d7\u30ed\u30c0\u30af\u30c8\u958b\u767a\u5ba4"
				}';

		//たまに空にしてそんな人居ないテスト
		//if(mt_rand(0, 10) < 3) $moto_data = array();

		$arr = array();
		if(count($moto_data) > 0) {
			$arr = json_decode($moto_data, true);
			$arr['TANTOSYA_CD'] = $arr['TANTOSYA_CD'] ."_". mt_rand(1000,9999);
			$arr['TANTOSYA_NM'] = $arr['TANTOSYA_NM'] ."_". mt_rand(1000,9999);
			$arr['BU_CD']       = $arr['BU_CD'] ."_". mt_rand(1000,9999);
			$arr['KA_CD']       = $arr['KA_CD'] ."_". mt_rand(1000,9999);
			$arr['HAN_CD']      = $arr['HAN_CD'] ."_". mt_rand(1000,9999);
			$arr['EIGYOSHO_NM'] = $arr['EIGYOSHO_NM'] ."_". mt_rand(1000,9999);
		}

		$this->view->data = $arr;
        header("Content-Type: application/json; charset=utf-8");
        echo $this->getparam("callback") ."(". json_encode($arr) .")";
        exit();
	}


}
