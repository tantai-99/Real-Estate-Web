<?php
// パス
$path = $viewHelper->_view->viewPath.$viewHelper->_view->device.parse_url($url, PHP_URL_PATH).'index.html';

// dom取得
$dom = new DOMDocument();
$html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// タイトル
$node = $xpath->query('//h4[@class="element-heading2 title"]')->item(0);
$title = $node !== null ? $node->nodeValue : '';

// エリア
$node = $xpath->query('//div[@class="element element-tximg7"]/p[@class="fs12"]')->item(0);
$area = $node !== null ? $node->nodeValue : '';

// 物件種目
$node = $xpath->query('//dd[@class="structure_type"]')->item(0);
$structure_type = $node !== null ? $node->nodeValue : '';

//// 日付
//$node = $xpath->query('//p[@class="element-date"]')->item(0);
//$date = $node !== null ? $node->nodeValue : '';

// 画像
$node = $xpath->query('//p[@class="element-right"]/img/@src')->item(0);
$image_src = $node !== null ? $node->nodeValue : '';

$node = $xpath->query('//p[@class="element-right"]/img/@alt')->item(0);
$image_alt = $node !== null ? $node->nodeValue : '';

// 氏名
$node = $xpath->query('//h3[@class="element-heading"]')->item(0);
$customer_name = $node !== null ? $node->nodeValue : '';

// 年齢
$node = $xpath->query('//dd[@class="customer_age"]')->item(0);
$customer_age = $node !== null ? $node->nodeValue : '';

// コメント
$node = $xpath->query('//section[@class="customer_comment"]')->item(0);
$customer_comment = $node !== null ? getInnerHtml($node) : '';
if ($customer_comment !== '') {
    // タイトル削除
    $customer_comment = preg_replace("/<h4.+?\/h4>/", "", $customer_comment);
}

// スタッフ氏名
$node = $xpath->query('//dd[@class="staff_name"]')->item(0);
$staff_name = $node !== null ? $node->nodeValue : '';

?>

<div class="element element-tximg7 <?php if ($index !== $last_index) echo 'element-line' ?>">
    <p class="element-right">
        <?php if ($image_src): ?>
            <img src="<?php echo $image_src ;?>" alt="<?php echo $image_alt ;?>"/>
        <?php endif ?>
    </p>
    <?php if ($area): ?>
        <p class="fs12"><?php echo htmlspecialchars($area) ?></p>
    <?php endif ?>

    <section>
        <?php if ($customer_name): ?>
            <h3 class="element-heading">
                <a href="<?php echo $url ?>"><?php echo htmlspecialchars($customer_name) ?></a>
            </h3>
        <?php endif ?>

        <?php if ($customer_age || $structure_type || $staff_name): ?>
            <dl class="area-profile">
                <?php if ($customer_age): ?>
                    <dt>年齢：</dt>
                    <dd><?php echo htmlspecialchars($customer_age) ?></dd>
                <?php endif ?>
                <?php if ($structure_type): ?>
                    <dt>ご契約種別：</dt>
                    <dd>
                        <?php echo htmlspecialchars($structure_type); ?>
                    </dd>
                <?php endif ?>
                <?php if ($staff_name): ?>
                    <dt>弊社担当：</dt>
                    <dd><?php echo htmlspecialchars($staff_name) ?></dd>
                <?php endif ?>
            </dl>
        <?php endif ?>
    </section>
    <section>
        <h4 class="element-heading2"><?php echo htmlspecialchars($title) ?></h4>
        <?php if ($customer_comment): ?>
            <?php echo $customer_comment ?>
        <?php endif ?>
    </section>
</div>