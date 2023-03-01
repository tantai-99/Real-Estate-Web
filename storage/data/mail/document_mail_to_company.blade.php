<?php echo($view->memberName)  ?>　御中

貴店ホームページをご覧になった【お客さまからのお問合せ】です。
下記の内容をご確認のうえ、お客さまへのご連絡をお願いいたします。

なお、お問合せ情報の中には、ご本人の個人情報が含まれていますので、お取り扱いには十分にご留意ください。
（本個人情報は、資料の送付、問合せに対する連絡以外の目的では利用できません。）


お問合せ日時　　　　：<?php echo($view->InquiryDatetime)."\n" ?>
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

