<?php
namespace App\Rules;
/**
 * Youtubeコードのバリデーション
 *
 *
 */
class YoutubeCode extends CustomRule
{

    const INVALID_CODE = 'InvalidCode';
    const MSG  = '「Youtube」以外のコードは埋め込めません。';

    const VAL_CHECK_HOST = 'www.youtube.com,www.youtube-nocookie.com';
    const DOM_ID = 'youtube_code';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID_CODE => self::MSG,
    );

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        $clearTag=trim(strip_tags($value));
        if(!$clearTag) {
            try {
                $dom = str_get_html('<div id="' . self::DOM_ID . '">' . $valueString . '</div>');

                $tagList = $dom->find('#'.self::DOM_ID)[0]->children;
                // ①タグ数が1であることを確認
                if(count($tagList) != 1) {
                    $this->invokableRuleError($fail, self::INVALID_CODE);
                    return false;
                }
                // ②iframe[@src]が存在することを確認
                $eachTags = [];
                foreach ($tagList as $tag) {
                    $eachTags[] = $tag;
                }
                if($eachTags[0]->tag != 'iframe' || empty($eachTags[0]->getAttribute('src'))) {
                    $this->invokableRuleError($fail, self::INVALID_CODE);
                    return false;
                }
                // ③srcの正当性をチェックする  
                $panoUrl = $eachTags[0]->getAttribute('src');
                // srcをパースし、ホスト名をチェック
                $url = parse_url($panoUrl);
                if(!isset($url['host']) || !in_array($url['host'], explode(",", self::VAL_CHECK_HOST))) {
                    $this->invokableRuleError($fail, self::INVALID_CODE);
                    return false;
                }
                // ④iframeタグの属性に、イベントハンドラ(onXXXX)が無いことを確認する
                foreach($eachTags[0]->attributes as $attr) {
                    if(preg_match('/^on[a-z]{1,}$/', $attr->nodeName)) {
                        $this->invokableRuleError($fail, self::INVALID_CODE);
                        return false;
                    }
                }
                // ⑤iframeのタグに設定できる属性は 
                foreach($eachTags[0]->attributes as $attr) {
                    if(!in_array($attr->nodeName, ['width', 'height', 'src', 'frameborder', 'allow', 'allowfullscreen'])) {
                        $this->invokableRuleError($fail, self::INVALID_CODE);
                        return false;
                    }
                }
            } catch(\Exception $e) {
                $this->invokableRuleError($fail, self::INVALID_CODE);
                return false;
            }
        } else {
            $this->invokableRuleError($fail, self::INVALID_CODE);
            return false;
        }
        return true;
    }
}