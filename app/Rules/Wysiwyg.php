<?php
namespace App\Rules;
/**
 * WYSIWYGエディタを利用したフォームのバリデーション
 * XSS対策のため、特定タグが検知された場合はエラーにする
 *
 */
class Wysiwyg extends CustomRule
{

    const INVALID = 'Invalid';
    const INVALID_ATTRIBUTE = 'invalid_attribute';

    const MSG = '利用できないタグ(scirpt,iframe,object)が検出されました。';

    // 不正とみなすタグの一覧
    private $invalidTags  = [ 'script', 'iframe', 'object' ];

    // 不正とみなす属性一覧：イベントハンドラ
    private $invalidAttrs = [ 'onabort', 'onblur', 'onchange', 'onclick', 'ondblclick',
                              'onerror', 'onfocus', 'oninput', 'onkeydown', 'onkeypress',
                              'onkeyup', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover',
                              'onmouseup', 'onmove', 'onload', 'onreset', 'onresize',
                              'onscroll', 'onselect', 'onsubmit', 'onunload' ];

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => self::MSG,
        self::INVALID_ATTRIBUTE => '利用できない属性(イベントハンドラ)が検出されました。',
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
        // 未入力の場合はタグチェック不要。必須チェックは別Validationで行う
        if (isEmpty($value)) {
            return true;
        }

        // 入力値の取得
        $valueString = (string)$value;
        try {
            // 内部のnull(\0)文字を置換
            $valueString = str_replace("\0", "", $valueString);

            // 全体をdivで囲ったうえでDOMパーサーで分解
            $wwDom = str_get_html('<div>' . $valueString . '</div>');

            foreach($this->invalidTags as $invalidTag) {
                $result = null;
                $result = $wwDom->find($invalidTag);

                if(count($result)) {
                    // error_log("Find Invalid-Tag:". $invalidTag);
                    $this->invokableRuleError($fail, self::INVALID);
                    return false;
                }
            }

            foreach($this->invalidAttrs as $invalidAttr) {
                $result = null;
                // $result = $wwDom->query("//*[@" . $invalidAttr . "]");
                $result = $wwDom->find($invalidAttr);

				if(count($result)) {
                    $this->invokableRuleError($fail, self::INVALID_ATTRIBUTE);
                    return false;
                }
            }
        } catch(Exception $e) {
            // DOMパーサーで失敗した場合の保険処理
            // script, iframeタグを 正規表現で検知する

            // すべて小文字に変換 -> 改行コード削除
            $valueStringConv = str_replace(array("\r\n", "\r", "\n"), '', mb_strtolower($valueString));

            foreach($this->invalidTags as $invalidTag) {
                if( preg_match("/\<" . $invalidTag . "\s+/", $valueStringConv)
                 || preg_match("/\<" . $invalidTag . "\>/", $valueStringConv) ) { 
                    error_log("Find Invalid-Tag:". $invalidTag);
                    $this->invokableRuleError($fail, self::INVALID);
                    return false;
                }
            }
        }
        return true;
    }
}
