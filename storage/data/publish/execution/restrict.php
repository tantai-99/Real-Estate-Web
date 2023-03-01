<?php
// Query-Stringを取り除く
$path = explode('?', $_SERVER['REQUEST_URI']);
$reqFile = sprintf("%s%s", dirname(dirname(__FILE__)), urldecode($path[0]));
$homeDir = dirname(dirname(__FILE__));
$outDir = dirname(urldecode($path[0]));
// ファイルが自分自身(restrict.php)なら 404
if($reqFile == __FILE__) {
    outputNotFound();
}
// 公開対象出ない場合は 404
if (strpos($outDir, 'files') !== false) {
    $filename = str_replace(dirname(__FILE__) . '/' , '', $reqFile);
    $outDir = dirname($outDir);
} else {
    $filename = basename($reqFile);
}
if(!in_array($filename, explode("\n", file_get_contents($homeDir . $outDir . "/public_files.txt")))) {
    outputNotFound();
}
try {
    $info = pathinfo($filename);
    $contentType = _contentType($info['extension']);
    if ($contentType === null) {
        $contentType = mime_content_type($reqFile);
    }
} catch(Exception $e) {
    $contentType = 'text/plain';
}
// ファイルを読み込む
$binData = file_get_contents($reqFile);
// 1.Content-typeの出力
header("Content-type: " . $contentType);
// 2.Last-Modifedの出力
header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($reqFile))." GMT");
// 3.キャッシュ有効化(1時間)
$expires = 3600 * 1;
header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + $expires));
header('Cache-Control: private, max-age=' . $expires);
header('Pragma: ');
// 4.データ長
header("Content-Length: ". strlen($binData));
print $binData;
exit;
/**
 * outputNotFound
 * 拡張子を削除して、リダイレクトさせる
 */
function outputNotFound() {
    $pathInfo = pathinfo($_SERVER['REQUEST_URI']);
    if (isset($pathInfo['extension'])) {
        $redirectTo = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '/';
        header( "Location: " . $redirectTo );
    }
    exit;
}

/**
 * 拡張子からContent-Typeを判断
 */
function _contentType($ext) {
    switch ($ext) {
        case 'pdf':
            return 'application/pdf';
        case 'xls':
        case 'xlsx':
            return 'application/vnd.ms-excel';
        case 'doc':
        case 'docx':
            return 'application/msword';
        case 'ppt':
        case 'pptx':
            return 'application/vnd.ms-powerpoint';
        default:
            return null;
    }
}