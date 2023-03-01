<?php
// パス
$path = $viewHelper->_view->viewPath.$viewHelper->_view->device.parse_url($url, PHP_URL_PATH).'index.html';

// dom取得
$dom = new DOMDocument();
$html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// タイトル
$node = $xpath->query('//h2[@class="heading-lv1"]')->item(0);
$title = $node !== null ? $node->nodeValue : '';
if($title == "") {
    $node = $xpath->query('//h2[@class=" heading-lv1-1column"]/span')->item(0);
    $title = $node !== null ? $node->nodeValue : '';
}

// 画像
$node = $xpath->query('//div[@class="element-parts"]/img/@src')->item(0); //2column
if ($node === null) {
    $node = $xpath->query('//div[@class="element tac"]/img/@src')->item(0); //1column
}
$image_src = $node !== null ? $node->nodeValue : '';

$node = $xpath->query('//div[@class="element-parts"]/img/@alt')->item(0); //2column
if ($node === null) {
    $node = $xpath->query('//div[@class="element tac"]/img/@alt')->item(0); //1column
}
$image_alt = $node !== null ? $node->nodeValue : '';

// コメント
$node = $xpath->query('//div[@class="element pr_comment"]')->item(0);
$pr = $node !== null ? getInnerHtml($node) : '';


// 住所
$node = $xpath->query('//td[@class="adress"]')->item(0);
$adress = $node !== null ? $node->nodeValue : '';

// TEL
$node = $xpath->query('//td[@class="tel"]')->item(0);
$tel = $node !== null ? $node->nodeValue : '';

?>

<section>
    <h3 class="heading-lv2"><a href="<?php echo $url; ?>"><?php echo $title; ?></a></h3>
    <div class="element <?php if ($image_src) echo 'element-tximg1'; ?>">
        <?php if ($image_src): ?>
            <p class="element-right">
                <img src="<?php echo $image_src ?>" alt="<?php echo $image_alt ?>"/>
            </p>
        <?php endif ?>
        <?php if ($pr): ?>
            <div class="element-left">
                <?php echo $pr ?>
            </div>
        <?php endif ?>
    </div>

    <div class="element">
        <table class="element-table element-table1">
            <tr>
                <th>住所</th>
                <td><?php echo $adress; ?></td>
            </tr>
            <tr>
                <th>TEL</th>
                <td><?php echo $tel; ?></td>
            </tr>
        </table>
    </div>
</section>
