<?php

// パス
$path = $viewHelper->_view->viewPath.$viewHelper->_view->device.parse_url($url, PHP_URL_PATH).'index.html';

// dom取得
$dom = new DOMDocument();
$html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// 店舗名、役職
$node = $xpath->query('//p[@class="fs12"]')->item(0);
$shop_name_and_position = $node !== null ? $node->nodeValue : '';

// 氏名
$node = $xpath->query('//h3[@class="element-heading"]')->item(0);
$name = $node !== null ? getInnerHtml($node) : '';

// 出身地
$node = $xpath->query('//dd[@class="birthplace"]')->item(0);
$birthplace = $node !== null ? $node->nodeValue : '';

// 趣味
$node = $xpath->query('//dd[@class="hobby"]')->item(0);
$hobby = $node !== null ? $node->nodeValue : '';

// 資格
$node = $xpath->query('//dd[@class="qualification"]')->item(0);
$qualification = $node !== null ? $node->nodeValue : '';

// PR
$node = $xpath->query('//div[@class="rp_comment"]')->item(0);
$rp_comment = $node !== null ? getInnerHtml($node) : '';

$node = $xpath->query('//p[@class="element-right"]/img/@src')->item(0);
$img_src = $node !== null ? $node->nodeValue : '';

$node = $xpath->query('//p[@class="element-right"]/img/@alt')->item(0);
$img_alt = $node !== null ? $node->nodeValue : '';

?>
<div class="element element-tximg2 <?php if ($index !== $last_index) echo 'element-line' ?>">
    <div class="element-left">

        <?php if ($shop_name_and_position) : ?>
            <p class="fs12">
                <?php echo $shop_name_and_position; ?>
            </p>
        <?php endif ?>
        <section>
            <?php if ($name): ?>
                <h3 class="element-heading">
                    <a href="<?php echo $url ?>"><?php echo $name; ?></a>
                </h3>
            <?php endif ?>

            <?php if ($birthplace || $hobby || $qualification): ?>
                <dl class="area-profile">
                    <?php if ($birthplace): ?>
                        <dt>出身：</dt>
                        <dd><?php echo htmlspecialchars($birthplace) ?></dd>
                    <?php endif ?>
                    <?php if ($hobby): ?>
                        <dt>趣味：</dt>
                        <dd><?php echo htmlspecialchars($hobby) ?></dd>
                    <?php endif ?>
                    <?php if ($qualification): ?>
                        <dt>資格：</dt>
                        <dd>
                            <?php echo htmlspecialchars($qualification) ?>
                        </dd>
                    <?php endif ?>
                </dl>
            <?php endif ?>

            <?php if ($rp_comment) echo $rp_comment; ?>
        </section>
    </div>
    <p class="element-right">
        <?php if ($img_src): ?>
            <img src="<?php echo $img_src ?>" alt="<?php echo htmlspecialchars($img_alt) ?>"/>
        <?php endif ?>
    </p>
</div>