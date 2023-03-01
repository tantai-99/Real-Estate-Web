<?php
/**
 * パノラマコードのバリデーション
 *
 *
 */
namespace App\Rules;

class PanoramaCode extends CustomRule
{

    const INVALID = 'Invalid';
    const MSG  = '「アットホーム　VR内見・パノラマ」以外のコードは埋め込めません。';

    const VAL_CHECK_HOST = 'athome.jp';
    const DOM_ID = 'panorama_code';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => self::MSG,
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
        if ($valueString === '') {
            return true;
        }

        $this->_setValue($valueString);

        $clearTag=trim(strip_tags($value));
        if(!$clearTag) {
            try {
                $dom = str_get_html('<div id="' . self::DOM_ID . '">' . $valueString . '</div>');

                $tagList = $dom->find('#'.self::DOM_ID)[0]->children;
                // ①タグ数が1であることを確認
                if(count($tagList) != 1) {
                    $this->invokableRuleError($fail, self::INVALID);
                    return false;
                }
                // ②iframe[@src]が存在することを確認
                $eachTags = [];
                foreach ($tagList as $tag) {
                    $eachTags[] = $tag;
                }
                if($eachTags[0]->tag != 'iframe' || empty($eachTags[0]->getAttribute('src'))) {
                    $this->invokableRuleError($fail, self::INVALID);
                    return false;
                }
                // ③srcの正当性をチェックする  
                $panoUrl = $eachTags[0]->getAttribute('src');
                // srcをパースし、ホスト名をチェック
                $url = parse_url($panoUrl);
                if(!isset($url['host']) || !preg_match("/" . self::VAL_CHECK_HOST ."/", $url['host'])) {
                    $this->invokableRuleError($fail, self::INVALID);
                    return false;
                }
                // ④iframeタグの属性に、イベントハンドラ(onXXXX)が無いことを確認する
                foreach($eachTags[0]->attr as $key => $attr) {
                    if(preg_match('/^on[a-z]{1,}$/', $key)) {
                        $this->invokableRuleError($fail, self::INVALID);
                        return false;
                    }
                }
            } catch(Exception $e) {
                $this->invokableRuleError($fail, self::INVALID);
                return false;
            }
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
        return true;
    }
}