<?php
// パス
$path = $viewHelper->_view->viewPath.$viewHelper->_view->device.parse_url($url, PHP_URL_PATH).'index.html';

// dom取得
$dom = new DOMDocument();
$html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// タイトル
$node = $xpath->query('//div[@class="element-list-title"]')->item(0);
$title = "";
if ($node !== null) {  
    foreach ($node->childNodes as $key=>$childNode) {
        if ($node->childNodes->length == 1) {
            $title .= $dom->saveHTML($childNode);
        } else {
            if ($key == 0 || $key == $node->childNodes->length - 1 ) continue;
            $ensp = html_entity_decode("&ensp;");
            $nodeValue = html_entity_decode($childNode->nodeValue);
            $nodeValue = str_replace($ensp, "", $nodeValue);
            if (trim($nodeValue) != '') {
                $title .= $dom->saveHTML($childNode);
            } else {
                if ($key != $node->childNodes->length - 2 ) {
                    $title .= '<br>';
                }
            }
        }
    }
}
if ($title == "") {
    $node = $xpath->query('//h2[@class="heading-lv1"]/span')->item(0);
    $title = $node !== null ? $node->nodeValue : '';   
}
if($title == "") {
	$node = $xpath->query('//h2[@class=" heading-lv1-1column"]/span')->item(0);
	$title = $node !== null ? $node->nodeValue : '';
}
if($title == "") {
	$node = $xpath->query('//h2[@class=" heading-lv1 info"]/span')->item(0);
	$title = $node !== null ? $node->nodeValue : '';
}

// 日付
$node = $xpath->query('//p[@class="element-date"]')->item(0);
$date = $node !== null ? $node->nodeValue : '';

$newMark = $viewHelper->checkNewMark($pageIndex, date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $date))));

?>