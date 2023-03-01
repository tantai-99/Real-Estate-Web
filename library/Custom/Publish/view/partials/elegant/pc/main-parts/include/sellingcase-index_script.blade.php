<?php
// パス
$path = $viewHelper->_view->viewPath.$viewHelper->_view->device.parse_url($url, PHP_URL_PATH).'index.html';

// dom取得
$dom = new DOMDocument();
$html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// タイトル
$node = $xpath->query('//section[@class="sellingcase"]/h3[@class="heading-lv2"]')->item(0);
$title = $node !== null ? $node->nodeValue : '';
$parentSection = $node->parentNode;

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

// コメント
$node = $xpath->query('.//div[@class="element comment"]',$parentSection)->item(0);
$comment = $node !== null ? getInnerHtml($node) : '';

// 物件種目
$node = $xpath->query('.//td[@class="structure_type"]',$parentSection)->item(0);
$structure_type = $node !== null ? $node->nodeValue : '';

// 価格
$node = $xpath->query('.//td[@class="price"]',$parentSection)->item(0);
$price = $node !== null ? $node->nodeValue : '';

?>

<section>
    <h3 class="heading-lv2"><a href="<?php echo $url ;?>"><span><?php echo $title ;?></span></a></h3>
    <div class="element <?php if ($image_src) echo 'element-tximg1'; ?>">
        <?php if ($image_src): ?>
            <p class="element-right">
                <img src="<?php echo $image_src ?>" alt="<?php echo $image_alt ?>"/>
            </p>
        <?php endif ?>
        <?php if ($comment): ?>
            <div class="<?php if ($image_src) echo 'element-left' ?>">
                <?php echo $comment ?>
            </div>
        <?php endif ?>
    </div>
    <div class="element">
        <table class="element-table element-table1">
            <tr>
                <th>物件種目</th>
                <td>
                    <?php echo $structure_type ?>
                </td>
            </tr>
            <?php if ($price): ?>
                <tr>
                    <th>売却価格</th>
                    <td>
                        <?php echo htmlspecialchars($price) ?>
                    </td>
                </tr>
            <?php endif ?>
        </table>
    </div>
</section>