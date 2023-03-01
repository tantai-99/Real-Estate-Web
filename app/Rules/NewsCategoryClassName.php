<?php
namespace App\Rules;
use Library\Custom\View\TopOriginalLang;

class NewsCategoryClassName extends CustomRule {

    const IS_NUMERIC = 'is_numeric';
    const INVALID_FORMAT = 'invalid_format';
    const INVALID = 'invalid';
    const IS_EMPTY = 'isEmpty';


    /** @var Library\Custom\View\Helper\TopOriginalLang */
    protected $text;

    /** @var Modules\Admin\Http\Form\TopNotificationForm */
    protected $_table = null;

    public function __construct($options = array())
    {
        if (isset($options['table'])) {
            $this->setTable($options['table']);
        }
        $this->init();
    }

    public function setTable($table) {
        $this->_table = $table;
        return $this;
    }

    public function init() {
        // $broker = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        // $this->text = $broker->topOriginalLang();
        $this->text = new TopOriginalLang();
        $this->_messageTemplates = array(
            self::IS_EMPTY => $this->text->get('notification_settings.class.required'),
            self::INVALID =>  $this->text->get('notification_settings.class.invalid'),
            self::IS_NUMERIC => $this->text->get('notification_settings.class.is_numeric'),
            self::INVALID_FORMAT => $this->text->get('notification_settings.class.invalid_format'),
        );
    }


    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array();


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
        if(!$value){
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }

        if ($this->_table->checkInUseClassNameFromValidator($value)) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }

        $regex = '/^[\w\-]*$/';
        if (!preg_match($regex, $value, $matches)) {
            $this->invokableRuleError($fail, self::INVALID_FORMAT);
            return false;
        }

        $regexNumeric = '/(^-(?=[^0-9\-])|^[a-zA-Z\_]|^\-$)[0-9a-zA-Z\-\_]*/';
        if (!preg_match( $regexNumeric, $value, $matches)) {
            $this->invokableRuleError($fail, self::IS_NUMERIC);
            return false;
        }

        return true;
    }
}
