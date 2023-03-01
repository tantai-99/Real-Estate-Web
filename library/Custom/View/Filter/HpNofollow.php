<?php
namespace Library\Custom\View\Filter;
/**
 * <a>タグに rel="nofollow" をセットする
 */
class HpNofollow
{

    private $view;

    public function filter($buffer)
    {
        $domains = [
            'www.'.$this->view->company->domain,
            \Library\Custom\Publish\Ftp::getPublishName(config('constants.publish_type.TYPE_TESTSITE')) . '.' . $this->view->company->domain,
            \Library\Custom\Publish\Ftp::getPublishName(config('constants.publish_type.TYPE_SUBSTITUTE')) . '.' . $this->view->company->domain
        ];

        $cb = function ($matches) use ($domains) {
            if (strpos($matches[1], '//' . $domains[0]) !== false || // 自社ドメイン
                strpos($matches[1], '//' . $domains[1]) !== false || // 自社ドメイン (テスト)
                strpos($matches[1], '//' . $domains[2]) !== false || // 自社ドメイン (代理)
                (strpos($matches[1], 'http') !== 0 && strpos($matches[1], '//') !== 0) || // ローカルリンク
                strpos($matches[0], ' rel=') > 0 // 既にrel=アトリビュート有り
            ) {
                return $matches[0];
            }

            return $matches[0] . ' rel="nofollow"';
        };

        return preg_replace_callback('/<a[^>]+href=[\'"]([^\'"]+)[^>]+/', $cb, $buffer);
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}
