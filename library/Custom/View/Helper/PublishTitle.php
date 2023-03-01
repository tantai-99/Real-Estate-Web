<?php
namespace Library\Custom\View\Helper;

use App\Repositories\HpPage\HpPageRepository;

class PublishTitle extends  HelperAbstract {

    public function publishTitle(array $page) {

        // title
        $title = $page['title'];

        // filename
        switch ($page['page_type_code']) {
            case HpPageRepository::TYPE_TOP:
                $filename = null;
                break;
            case HpPageRepository::TYPE_ALIAS:
            case HpPageRepository::TYPE_LINK:
            case HpPageRepository::TYPE_LINK_HOUSE:
            case HpPageRepository::TYPE_ESTATE_ALIAS:
                $filename = 'リンク';
                break;
            default:
                $filename = $page['filename'];
                break;
        };

        // ガッチャンコ
        if ($filename) {
            $title .= " （{$filename}）";
        }

        return $title;
    }
}