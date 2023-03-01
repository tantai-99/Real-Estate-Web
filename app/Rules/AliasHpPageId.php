<?php

namespace App\Rules;

use App\Repositories\HpPage\HpPageRepository;

class AliasHpPageId extends HpPageId
{

    protected $_idCol = 'link_id';

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
        $isValid = parent::__invoke($attribute, $value, $fail);

        if (!$isValid) {
            return $isValid;
        }

        if (!$this->_row) {
            return false;
        }

        $category = $this->_table->getCategoryByType($this->_row->page_type_code);
        if ($category == HpPageRepository::CATEGORY_LINK) {
            $fail('ページIDが無効です。');
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'ページIDが無効です。';
    }
}
