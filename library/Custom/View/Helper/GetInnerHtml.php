<?php
namespace Library\Custom\View\Helper;

class GetInnerHtml extends  HelperAbstract {

    public function getInnerHtml() {

        echo <<<'EOD'
<?php
function getInnerHtml($node) {
    $children = $node->childNodes;
    $html = '';
    foreach ($children as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }
    return $html;
}
; ?>
EOD;
    }
}