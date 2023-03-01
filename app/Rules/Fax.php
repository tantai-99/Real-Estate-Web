<?php
/**
 * URLのバリデーション
 *
 *
 */
namespace App\Rules;

class Fax extends Tel
{

    const INVALID = 'Invalid';

    /**
     *  @var array
    protected $_messageTemplates = array(
        self::INVALID => '半角英数字、ハイフンで入力してください。'
    );
     */

}