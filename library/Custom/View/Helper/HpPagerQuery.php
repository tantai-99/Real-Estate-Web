<?php
namespace Library\Custom\View\Helper;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * ページャーリンクタグを生成
 */
class HpPagerQuery extends  HelperAbstract
{
    /**
     * @param int $page_id
     * @param int $link_page
     * @param string $blog_yyyymm
     * @return string
     */
    public function hpPagerQuery($page_id, $link_page, $blog_yyyymm = null)
    {
        $path = $this->_view->hpLink($page_id);
        if ($blog_yyyymm) {
        	$path .= $blog_yyyymm ."/";
        }
        $request = app('request');
        $is_preview = getActionName() === 'previewPage';

        if ($is_preview) {
            $parameters = app('request')->all();

            $parameters['preview_page_num'] = $link_page;
            $path .= '?' . http_build_query($parameters);
        }else if ($link_page !== 1){
            $path .= $link_page;
        }

        return $path;
    }
}