<?php
namespace Library\Custom\View\Helper;

class IncludeSide extends  HelperAbstract {

    public $data = [
        'other_link' => '',
        'customized_contents' => '',
        'article_link' => '',
    ];

    public function includeSide() {
        return $this;
    }

    public function captureStart() {
        ob_start();
    }

    public function captureEnd($name) {
        $this->data[$name] = ob_get_contents();
        ob_end_clean();
    }

    public function flush($contents) {

        if (getActionName() == 'previewPage') {
            $data = $this->data;
            echo eval('?>' . $contents . '<?php ');
            $this->clearData();
            return;
        }

        echo '<?php $sideContentsData = array("other_link"=>"","customized_contents"=>"", "article_link" => "");?>'."\n";
        foreach ($this->data as $name => $content) {
            echo "<?php ob_start();?>\n";
            echo $content . "\n";
            echo "<?php \$sideContentsData['{$name}'] = ob_get_contents();?>\n";
            echo "<?php ob_end_clean();?>\n";
        }
        echo '<?php $side_error = $this->viewHelper->includeSide($sideContentsData);?>'."\n";

        $this->clearData();
    }

    public function clearData() {
        $this->data = [
            'other_link' => '',
            'customized_contents' => '',
            'article_link' => '',
        ];
    }

}
