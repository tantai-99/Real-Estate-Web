<?php
/**
 *
 *
 *
 */
namespace App\Rules;

class AthomeKomaSrc extends CustomRule
{

    const INVALID = 'Invalid';

    const MSG = '検索エンジンレンタル以外のHTMLタグは埋め込めません。';

    const VAL_CHECK_CLASS = 'athome';
    const VAL_CHECK_HOST  = 'asp.athome.jp,asp.athome-stg401.jp,kensho01-serf-web-elb-1681418510.ap-northeast-1.elb.amazonaws.com,10.18.44.77,kensho01-serf.athome.jp';

    const DOM_ID = 'athome_koma_src';

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
        if (trim($valueString) === '') {
            return true;
        }

        $this->_setValue($valueString);

        $clearTag=trim(strip_tags($value));

        if(!$clearTag && (strlen($value) == strlen(utf8_decode($value))) ) {
            try {
                $dom = str_get_html('<div id="' . self::DOM_ID . '">' . $valueString . '</div>');

                $tagList = $dom->find('#'.self::DOM_ID)[0]->children;

                $res = false;
                if(count($tagList) == 1 || count($tagList) == 2) {
                    $eachTags = [];
                    foreach ($tagList as $tag) {
                        $eachTags[] = $tag;
                    }
                    if(count($tagList) == 1) {
                        $res = $this->_isValidForIframe($eachTags);
                    } else {
                        $res = $this->_isValidForDiv($eachTags);
                    }
                }
                if($res == false) {
                    $this->invokableRuleError($fail, self::INVALID);
                    return false;
                }
            } catch(\Exception $e) {
                $this->invokableRuleError($fail, self::INVALID);
                return false;
            }
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
        return true;
    }

    private function _isValidForIframe($eachTags)
    {
        // ①iframe[@src]が存在することを確認
        if($eachTags[0]->tag != 'iframe' || empty($eachTags[0]->getAttribute('src'))) {
            return false;
        }
        // ②srcの正当性をチェックする
        if(empty($eachTags[0]->getAttribute('src'))) {
            return false;
        }
        // srcをパースし、ホスト名をチェック
        $srcUrl = $eachTags[0]->getAttribute('src');
        $url = parse_url($srcUrl);
        if(!isset($url['host']) || !in_array($url['host'], explode(",", self::VAL_CHECK_HOST))) {
            return false;
        }
        // ③iframeタグの属性に、イベントハンドラ(onXXXX)が無いことを確認する
        foreach($eachTags[0]->attr as $key => $attr) {
            if(preg_match('/^on[a-z]{1,}$/', $key)) {
                return false;
            }
        }
        return true;
    }

    private function _isValidForDiv($eachTags)
    {
        // ①div ⇒ scriptの順に現れ、かつ両者の親のidが正しい(兄弟要素/self::DOM_ID)ことを確認
        if($eachTags[0]->tag != 'div' || $eachTags[1]->tag != 'script') {
            return false;
        }
        if($eachTags[0]->parent->getAttribute('id') != self::DOM_ID
        || $eachTags[1]->parent->getAttribute('id') != self::DOM_ID ) {
            return false;
        } 
        // ②divタグのclass要素に『athome』が含まれる
        if(empty($eachTags[0]->getAttribute('class'))) {
            return false;
        }
        $divClassList = explode(" ", $eachTags[0]->getAttribute('class'));
        if(!in_array(self::VAL_CHECK_CLASS, $divClassList)) {
            return false;
        }
        // ③scripタグのsrcチェック      
        if(empty($eachTags[1]->getAttribute('src'))) {
            return false;
        }
        // srcをパースし、ホスト名をチェック
        $srcUrl = $eachTags[1]->getAttribute('src');
        $url = parse_url($srcUrl);
        if(!isset($url['host']) || !in_array($url['host'], explode(",", self::VAL_CHECK_HOST))) {
            return false;
        }
        // ④div, script 両タグの属性に、イベントハンドラ(onXXXX)が無いことを確認する
        foreach($eachTags as $tag) {
            foreach($tag->attr as $key => $attr) {
                if(preg_match('/^on[a-z]{1,}$/', $key)) {
                    return false;
                }
            }
        }
        return true;
    }
}