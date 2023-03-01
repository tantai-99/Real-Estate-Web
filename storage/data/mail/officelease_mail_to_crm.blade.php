<?php echo($view->memberName)  ?>　　御中

貴店ホームページをご覧になった【お客さまからのお問合せ】です。
下記の内容をご確認のうえ、お客さまへのご連絡をお願いいたします。

なお、お問合せ情報の中には、ご本人の個人情報が含まれていますので、お取り扱いには十分にご留意ください。
（本個人情報は、資料の送付、問合せに対する連絡以外の目的では利用できません。）


お問合せ日時　　　　：<?php echo($view->InquiryDatetime)."\n" ?>
<?php if(array_key_exists('bukken',$view->inquiryParams)): ?>
----------------------------------------------------------------------
＜お問合せされた物件の概要＞
<?php foreach($view->inquiryParams['bukken'] as $key=>$val): ?>
<?php echo ($view->inquiryParams['label'][$key]."：".$val."\n");?>
<?php endforeach; ?>
<?php endif; ?>

<?php if(array_key_exists('second_estate',$view->inquiryParams)):?>
＜この物件は、2次広告自動公開の物件からの反響です。＞
不動産会社名（元付）　：<?php echo $view->inquiryParams['second_estate']['motokai_name']."\n"; ?>
電話番号（元付）　　　：<?php echo $view->inquiryParams['second_estate']['motokai_tel']."\n"; ?>
メールアドレス（元付）：<?php echo $view->inquiryParams['second_estate']['motokai_mail']."\n"; ?>

物件の詳細に関しましては、ＡＴＢＢ（不動産業務総合支援サイト）の物件入手の物件番号検索をご利用ください。
<ＡＴＢＢのアドレス>
ＵＲＬ：https://atbb.athome.jp/
<?php endif;?>
<?php if(array_key_exists('bukken_no',$view->inquiryParams)): ?>

<?php foreach($view->inquiryParams['bukken_no'] as $key=>$val): ?>
<?php if($key == 'kanri_no' && array_key_exists('second_estate',$view->inquiryParams)) { continue; } ?>
<?php echo ($view->inquiryParams['label'][$key]."：".$val."\n");?>
<?php endforeach; ?>
<?php endif; ?>

<?php if(array_key_exists('url',$view->inquiryParams)): ?>
詳しい物件情報はコチラ↓
<?php echo ($view->inquiryParams['url']."\n");?>
<?php endif; ?>

<?php if(array_key_exists('content',$view->inquiryParams)): ?>
----------------------------------------------------------------------
＜お問合せ内容＞
<?php foreach($view->inquiryParams['content'] as $key=>$val): ?>
<?php if($key==='remarks'){ continue; } ?>
<?php echo ("・".$val."\n");?>
<?php endforeach; ?>
<?php if(array_key_exists('remarks',$view->inquiryParams['content'])): ?>
［備考］
<?php echo($view->inquiryParams['content']['remarks']."\n") ?>
<?php endif; ?>
<?php endif; ?>

<?php if(array_key_exists('peripheral_flg',$view->inquiryParams) && ($view->inquiryParams['peripheral_flg'] == 1)):?>
＜周辺エリア情報＞
エリア情報の提供を希望する
※物件周辺のエリア情報を希望されています。不動産データプロ「レポート」などをご提供ください。
<?php endif; ?>

<?php if(array_key_exists('profile',$view->inquiryParams)): ?>
----------------------------------------------------------------------
＜お客さまのプロフィール＞
<?php foreach($view->inquiryParams['profile'] as $key=>$val): ?>
<?php echo ($view->inquiryParams['label'][$key]."：".$val."\n");?>
<?php endforeach; ?>
<?php endif; ?>
----------------------------------------------------------------------

お心あたりのない方は、恐れ入りますが

mailcenter@athome.jpまでメール本文を残したまま メール転送をお願いいたします。

◆◇ ………………………………………………… ◇◆
　アットホーム カスタマーセンター
　E-Mail : mailcenter@athome.jp
◆◇ ………………………………………………… ◇◆
