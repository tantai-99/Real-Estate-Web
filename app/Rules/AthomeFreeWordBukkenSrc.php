<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class AthomeFreeWordBukkenSrc implements InvokableRule
{
    const INVALID = 'Invalid';

    const MSG = '検索エンジンレンタル以外のHTMLタグは埋め込めません。';

    const VAL_CHECK_CLASS = 'athome';
    const VAL_CHECK_HOST  = 'asp.athome.jp,asp.athome-stg401.jp,kensho01-serf-web-elb-1681418510.ap-northeast-1.elb.amazonaws.com,10.18.44.77,kensho01-serf.athome.jp';

    const DOM_ID = 'athome_free_word_bukken_src';
    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

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
        if (trim($valueString) == '') {
            return true;
        }

        $value=strtolower($value);
        if(strlen($value) == strlen(utf8_decode($value))) {
            try {
                $dom = str_get_html('<div id="' . self::DOM_ID . '">' . $valueString . '</div>');
                $tagList = $dom->find('#'.self::DOM_ID)[0]->children;

                // ①タグ数が２であることを確認
                
                if(count($tagList) != 2) {
                    $fail(self::MSG);
                    return false;
                } 
                $eachTags = [];
                foreach($tagList as $tag) {
                    $eachTags[] = $tag;
                }

                // ②div ⇒ scriptの順に現れ、かつ両者の親のidが正しい(兄弟要素/self::DOM_ID)ことを確認
                if($eachTags[0]->tag != 'div' || $eachTags[1]->tag != 'script') {
                    $fail(self::MSG);
                    return false;
                }

                if($eachTags[0]->parent->getAttribute('id') != self::DOM_ID
                || $eachTags[1]->parent->getAttribute('id') != self::DOM_ID ) {
                    $fail(self::MSG);
                    return false;
                }
                // ③divタグのclass要素に『athome』が含まれる
                /* App\Rules\EmbeeSearchER でclassがあることは実証されている
                if(empty($eachTags[0]->getAttribute('class'))) {
                    $this->_error(self::INVALID);
                    return false;
                }
                */ 
                $divClassList = explode(" ", $eachTags[0]->getAttribute('class'));
                if(!in_array(self::VAL_CHECK_CLASS, $divClassList)) {
                    $fail(self::MSG);
                    return false;
                }
                // ④scripタグのsrcチェック
                /* App\Rules\EmbeeSearchER で srcがあることは実証されている
                if(empty($eachTags[1]->getAttribute('src'))) {
                    $this->_error(self::INVALID);
                    return false;
                }
                */
                $scriptSrc = $eachTags[1]->getAttribute('src');
                // srcをパースし、ホスト名をチェック
                $url = parse_url($scriptSrc);
                if(!isset($url['host']) || !in_array($url['host'], explode(",", self::VAL_CHECK_HOST))) {
                    $fail(self::MSG);
                    return false;
                }
                // ⑤div, script 両タグの属性に、イベントハンドラ(onXXXX)が無いことを確認する
                foreach($eachTags as $tag) {
                    foreach($tag->attr as $key => $attr) {
                        if(preg_match('/^on[a-z]{1,}$/', $key)) {
                            $fail(self::MSG);
                            return false;
                        }
                    }
                }
                // ⑥scriptのタグに設定できる属性は async, src
                foreach($eachTags[1]->attr as $key => $attr) {
                    if(!in_array($key, ['src', 'async'])) {
                        $fail(self::MSG);
                        return false;
                    }
                }
            } catch(\Exception $e) {
                // DOMパース失敗
                $fail(self::MSG);
                return false;
            }
        } else {
            // マルチバイトコードの入力あり
            $fail(self::MSG);
            return false;
        }
        return true;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    // public function passes($attribute, $value)
    // {
    //     $valueString = (string) $value;

    //     if ($valueString === '') {
    //         return true;
    //     }
    //     $this->_setValue($valueString);

    //     $value=strtolower($value);
    //     if(strlen($value) == strlen(utf8_decode($value))) {
    //         try {
    //             $dom = str_get_html('<div id="' . self::DOM_ID . '">' . $valueString . '</div>');
    //             $tagList = $dom->find('#'.self::DOM_ID)[0]->children;

    //             // ①タグ数が２であることを確認
    //             if(count($tagList) != 2) {
    //                 return false;
    //             } 
    //             $eachTags = [];
    //             foreach($tagList as $tag) {
    //                 $eachTags[] = $tag;
    //             }

    //             // ②div ⇒ scriptの順に現れ、かつ両者の親のidが正しい(兄弟要素/self::DOM_ID)ことを確認
    //             if($eachTags[0]->tag != 'div' || $eachTags[1]->tag != 'script') {
    //                 $this->_error(self::INVALID);
    //                 return false;
    //             }

    //             if($eachTags[0]->parent->getAttribute('id') != self::DOM_ID
    //             || $eachTags[1]->parent->getAttribute('id') != self::DOM_ID ) {
    //                 $this->_error(self::INVALID);
    //                 return false;
    //             }
    //             // ③divタグのclass要素に『athome』が含まれる
    //             /* App\Rules\EmbeeSearchER でclassがあることは実証されている
    //             if(empty($eachTags[0]->getAttribute('class'))) {
    //                 $this->_error(self::INVALID);
    //                 return false;
    //             }
    //             */ 
    //             $divClassList = explode(" ", $eachTags[0]->getAttribute('class'));
    //             if(!in_array(self::VAL_CHECK_CLASS, $divClassList)) {
    //                 $this->_error(self::INVALID);
    //                 return false;
    //             }
    //             // ④scripタグのsrcチェック
    //             /* App\Rules\EmbeeSearchER で srcがあることは実証されている
    //             if(empty($eachTags[1]->getAttribute('src'))) {
    //                 $this->_error(self::INVALID);
    //                 return false;
    //             }
    //             */
    //             $scriptSrc = $eachTags[1]->getAttribute('src');
    //             // srcをパースし、ホスト名をチェック
    //             $url = parse_url($scriptSrc);
    //             if(!isset($url['host']) || !in_array($url['host'], explode(",", self::VAL_CHECK_HOST))) {
    //                 $this->_error(self::INVALID);
    //                 return false;
    //             }
    //             // ⑤div, script 両タグの属性に、イベントハンドラ(onXXXX)が無いことを確認する
    //             foreach($eachTags as $tag) {
    //                 foreach($tag->attr as $key => $attr) {
    //                     if(preg_match('/^on[a-z]{1,}$/', $key)) {
    //                         $this->_error(self::INVALID);
    //                         return false;
    //                     }
    //                 }
    //             }
    //             // ⑥scriptのタグに設定できる属性は async, src
    //             foreach($eachTags[1]->attr as $key => $attr) {
    //                 if(!in_array($key, ['src', 'async'])) {
    //                     $this->_error(self::INVALID);
    //                     return false;
    //                 }
    //             }
    //         } catch(Exception $e) {
    //             // DOMパース失敗
    //             $this->_error(self::INVALID);
    //             return false;
    //         }
    //     } else {
    //         // マルチバイトコードの入力あり
    //         $this->_error(self::INVALID);
    //         return false;
    //     }
    //     return true;
    // }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '検索エンジンレンタル以外のHTMLタグは埋め込めません。';
    }
}
