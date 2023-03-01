<?php
// パス
$path = $viewHelper->_view->viewPath.$viewHelper->_view->device.parse_url($url, PHP_URL_PATH).'index.html';

// dom取得
$dom = new DOMDocument();
$html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// タイトル
$node = $xpath->query('//h3[@class="heading-lv2"]')->item(0);
$title = $node !== null ? $node->nodeValue : '';
$parentSection = $node->parentNode;

// コメント
$node = $xpath->query('.//div[@class="element comment"]',$parentSection)->item(0);
$comment = $node !== null ? getInnerHtml($node) : '';

// 画像
$node = $xpath->query('.//div[@class="element-parts"]/img/@src',$parentSection)->item(0);
if ($node === null) {
    $node = $xpath->query('.//div[@class="element tac"]/img/@src',$parentSection)->item(0); //1column
}
$image_src = $node !== null ? $node->nodeValue : '';

$node = $xpath->query('.//div[@class="element-parts"]/img/@alt',$parentSection)->item(0);
if ($node === null) {
    $node = $xpath->query('.//div[@class="element tac"]/img/@alt',$parentSection)->item(0); //1column
}
$image_alt = $node !== null ? $node->nodeValue : '';

// 期日
$node = $xpath->query('.//td[@class="date"]',$parentSection)->item(0);
$date = $node !== null ? $node->nodeValue : '';

// 住所
$node = $xpath->query('.//td[@class="adress"]',$parentSection)->item(0);
$adress = $node !== null ? $node->nodeValue : '';

?>

<section>
    <h3 class="heading-lv2"><a href="<?php echo $url; ?>"><?php echo $title; ?></a></h3>

    <div class="element <?php if ($image_src) echo 'element-tximg1'; ?>">
        <?php if ($image_src): ?>
            <p class="element-right">
                <img src="<?php echo $image_src ;?>" alt="<?php echo $image_alt ;?>">
            </p>
        <?php endif ?>
        <div class="<?php if ($image_src) echo 'element-left' ?>">
            <?php echo $comment ?>
        </div>
    </div>

    <div class="element">
        <table class="element-table element-table1">
            <?php if ($date): ?>
                <tr>
                    <th>
                        開催期間
                    </th>
                    <td>
                        <?php
                        if ($date) echo htmlspecialchars($date);
                        ?>
                    </td>
                </tr>
            <?php endif ?>
            <?php if ($adress): ?>
                <tr>
                    <th>
                        所在地
                    </th>
                    <td>
                        <?php echo htmlspecialchars($adress) ?>
                    </td>
                </tr>
            <?php endif ?>
        </table>
    </div>
</section>