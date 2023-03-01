<?php
namespace Modules\V1api\Services\Sp\Element;

class Choson
{

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
        $searchAreaElem = pq("<div />");
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

            $sectionElem = pq("<dt>{$shikugunName}</dt>");
            $sectionElem->append('<dd />');
            
            // $ulElem = pq('<ul>');
            $ulElem = pq('<ul class="list-select-set">');
            // すべての物件を見るを付加。
            $allElem = 
                '<li class="check-all">
                    <label>
                        <span class="checkbox"><input type="checkbox" name="shikugun_ct" value="' . $shikugunRoman . '"></span>
                        <span class="name">すべて</span>
                    </label>
                </li>';
            $ulElem->append($allElem);

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

                $liElem['li']->addClass('select-area');

                $labelElem = pq('<label>');
                $spanElem = "<span class='checkbox'><input type='checkbox' name='choson_ct' value='{$shikugunRoman}-{$chosonCode}'></span>";
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='name'>{$chosonName}</span>");
                $labelElem->append($spanElem);
                $spanElem = pq("<span class='num'>({$chosonCount})</span>");
                $labelElem->append($spanElem);                    
                $liElem->append($labelElem);
                
                if ($chosonCount == '0') {
                    $liElem['input']->attr('disabled', 'disabled');
                    $liElem->addClass('tx-disable');
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
                    $ulElem->append(sprintf("<li style=\"display:block;margin-top:13px;\"><h4 class=\"heading-lv2-1column\">%s</h4></li>", $chosons['gyo']));
                    foreach($chosons['chosons'] as $choson) {
                        $ulElem->append($choson);
                    }
                }
            }
            // 物件がゼロのエリアは表示しない。
//            if ($countLocate != 0) {
            if (true) {
                $sectionElem['dd']->append($ulElem);
                $searchAreaElem->append($sectionElem);
            }
        }
        
        return $searchAreaElem;
    }
}