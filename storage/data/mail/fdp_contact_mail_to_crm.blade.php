<?php echo($view->memberName)  ?>　御中

貴社ホームページをご覧になった【お客様からのお問合せ】です。
下記の内容をご確認のうえ、お客様へのご連絡をお願いいたします。

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
＜お客様のプロフィール＞
<?php foreach($view->inquiryParams['profile'] as $key=>$val): ?>
<?php echo ($view->inquiryParams['label'][$key]."：".$val."\n");?>
<?php endforeach; ?>
<?php endif; ?>

■こちらはホームページ作成ツールからのメールです。
　お心あたりのない方は、恐れ入りますが TEL 0120-134-855 まで
　ご連絡をお願いいたします。


◆◇ ………………………………………………… ◇◆
　アットホーム カスタマーセンター
　TEL : 0570-01-1967
　※PHS・IP電話の方は、TEL.045-330-3410 まで
　E-Mail : mailcenter@athome.jp
◆◇ ………………………………………………… ◇◆

