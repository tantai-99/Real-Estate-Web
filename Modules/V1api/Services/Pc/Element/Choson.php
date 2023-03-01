<?php
namespace Modules\V1api\Services\Pc\Element;
class Choson
{
    protected $logger;
    protected $_config;
    
    public function __construct() {
        // クラス名からモジュール名を取得
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);
        
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    /**
     * 駅選択の要素を作成して返します。 
     *
     * @param $chosonList 町村一覧の検索結果
     * @param $type_ct 物件種目のローマ字  (URL生成に使用)　特集は特集パス
     * @param $ken_ct 都道府県のローマ字
     * @param $isModal モーダルWindowならtrue
     * @param array $shikugunMap 検索パラメータから取得したshikugunデータ
     * @return 駅選択のtable要素
     */	
    public function createElement($chosonList, $type_ct, $ken_ct, $isModal, $shikugunMap)
    { 
        // 必要要素の初期化とテンプレ化
        $searchAreaElem = pq("<div class='element element-search-area' />");
        if (is_null($chosonList)) return $searchAreaElem;

        $iniStr = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤ　ユ　ヨラリルレロワ　ヲ　ン';

        // 駅一覧情報の作成
        foreach ($chosonList as $shikugun)
        {
            if (!isset($shikugunMap[$shikugun['shikugun_cd']])) {
                continue;
            }
            $shikugunData = $shikugunMap[$shikugun['shikugun_cd']];
            $shikugunCode = $shikugunData->code;
            $shikugunName = $shikugunData->shikugun_nm;
            $shikugunRoman = $shikugunData->shikugun_roman;
            
            $sectionTxt = '<section '. ($isModal ? '' : 'class="element-search-area-item" ' ) . '/>';
            $sectionElem = pq($sectionTxt);
            $sectionElem->append('<h4 class="heading-area"/>');
            $inputElem = "<label for='CHOSON_SHIKUGUN_{$shikugunCode}'>" .
                "<input type='checkbox' name='shikugun_ct' value='{$shikugunRoman}' id='CHOSON_SHIKUGUN_{$shikugunCode}'/>" .
                "{$shikugunName}</label>";
            $sectionElem['h4']->append($inputElem);
            
            $ulElem = pq('<ul>');

            // 市区郡ごとにリセット
            $choson_kanas = [
                '0'  => [ 'gyo' => 'あ行',   'chosons' => [] ],
                '1'  => [ 'gyo' => 'か行',   'chosons' => [] ],
                '2'  => [ 'gyo' => 'さ行',   'chosons' => [] ],
                '3'  => [ 'gyo' => 'た行',   'chosons' => [] ],
                '4'  => [ 'gyo' => 'な行',   'chosons' => [] ],
                '5'  => [ 'gyo' => 'は行',   'chosons' => [] ],
                '6'  => [ 'gyo' => 'ま行',   'chosons' => [] ],
                '7'  => [ 'gyo' => 'や行',   'chosons' => [] ],
                '8'  => [ 'gyo' => 'ら行',   'chosons' => [] ],
                '9'  => [ 'gyo' => 'わ行',   'chosons' => [] ],
                '99' => [ 'gyo' => 'その他', 'chosons' => [] ],
            ];

            $countLocate = 0;
            foreach ($shikugun['chosons'] as $choson)
            {
                $chosonCode  = $choson['code'];
                $chosonName  = $choson['choson_nm'];
                $chosonKanaInitial  = mb_convert_kana(mb_substr($choson['choson_kana_nm'], 0, 1), "K"); // カナ1文字目を全角変換
                $chosonCount  = $choson['count'];
                $countLocate += $chosonCount;
                // li要素の新規作成
                $liElem = pq('<li>');
                if ($isModal) {
                    $inputText = "<input type='checkbox' name='choson_ct' value='{$shikugunRoman}-{$chosonCode}'"
                        . " id='CHOSON_{$shikugunRoman}_{$chosonCode}'>";
                    $liElem->append($inputText);
                    $labelElem = pq("<label for='CHOSON_{$shikugunRoman}_{$chosonCode}'>");
                    if ($chosonCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $labelElem->addClass('tx-disable')->append("{$chosonName}(".number_format($chosonCount).")");
                    } else {
                        $labelElem->append("{$chosonName}(".number_format($chosonCount).")");
                    }
                    $liElem->append($labelElem);
				} else {
                    $inputText = "<input type='checkbox' name='choson_ct' value='{$shikugunRoman}-{$chosonCode}'>";
                    $liElem->append($inputText);
                    $spanElem = pq('<span>');
                    if ($chosonCount == '0') {
                        $liElem['input']->attr('disabled', 'disabled');
                        $spanElem->addClass('tx-disable')->text($chosonName . "(".number_format($chosonCount).")");
                     } else {
                        $ekiUrl = "/${type_ct}/${ken_ct}/result/{$shikugunRoman}-{$chosonCode}.html";
                        $spanElem->append("<a href='${ekiUrl}'>{$chosonName}</a>(".number_format($chosonCount).")");
                    }
                    $liElem->append($spanElem);
				}

                $kanaOffset = mb_strpos($iniStr, $chosonKanaInitial);
                if(strlen($kanaOffset) == 0) {
                    $choson_kanas['99']['chosons'][] = $liElem;
                } else {
                    $choson_kanas[ strval(intval($kanaOffset / 5)) ]['chosons'][] = $liElem;
                }

                // $ulElem->append($liElem);
            }
            foreach($choson_kanas as $chosons) {
                if(count($chosons['chosons']) > 0) {
                    $ulElem->append(sprintf("<li style=\"display:block;\"><h4>%s</h4></li>", $chosons['gyo']));
                    foreach($chosons['chosons'] as $choson) {
                        $ulElem->append($choson);
                    }
                }
            }
            
            // すべての物件を見るのリンクを付加。
            if ($countLocate != 0 && ! $isModal) {
                $lineUrl = "/${type_ct}/${ken_ct}/result/{$shikugunRoman}-city.html";
                $sectionElem->append("<p class='link-all-result'><a href='${lineUrl}'>${shikugunName}すべての物件を見る</a></p>");
            }

            $sectionElem->append($ulElem);            
            $searchAreaElem->append($sectionElem);
        }
        
        return $searchAreaElem;
    }
}