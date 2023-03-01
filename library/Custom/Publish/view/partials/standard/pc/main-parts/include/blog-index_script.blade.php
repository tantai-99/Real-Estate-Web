<?php
// パス
$path = $viewHelper->_view->viewPath.$viewHelper->_view->device.parse_url($url, PHP_URL_PATH).'index.html';

// dom取得
$dom = new DOMDocument();
$html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// タイトル
$node = $xpath->query('//h2[@class="heading-lv1"]/span')->item(0);
$title = $node !== null ? $node->nodeValue : '';
if($title == "") {
	$node = $xpath->query('//h2[@class=" heading-lv1-1column"]/span')->item(0);
	$title = $node !== null ? $node->nodeValue : '';
}

// 日付
$node = $xpath->query('//p[@class="element-date"]')->item(0);
$date = $node !== null ? $node->nodeValue : '';

?>