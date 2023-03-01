<?php

namespace Library\Custom\Form;

use Validator;

class Element
{

    protected $name;

    protected $label;

    protected $attributes = [];

    protected $_value;

    protected $messages = [];

    protected $_belongsTo;

    protected $_isRequired = false;

    protected $_validators = [];

    protected $_description;

    protected $isArray = false;

    protected $_messagesRules = [];

    protected $_allowEmpty = true;

    protected $_isValidRequired = false;

    protected $_registerInArrayValidator = false;

    public function __construct($name)
    {
        $this->name = $name;
        $this->setRequired(false);
    }

    public function setName($name)
    {
        return $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullName()
    {
        if ($this->getAttribute('name')) {
            return $this->getAttribute('name');
        }
        $name = $this->getName();

        if (!is_null($belongTo = $this->getBelongsTo())) {
            $name = $belongTo . '[' . $name . ']';
        }
        if ($this->isArray()) {
            $name = $name . '[]';
        }

        return $name;
    }

    public function isArray()
    {
        return $this->isArray;
    }

    /**
     * Get element id
     *
     * @return string
     */
    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
        }

        $id = $this->getFullName();

        // Bail early if no array notation detected
        if (!strstr($id, '[')) {
            return $id;
        }

        // Strip array notation
        if ('[]' == substr($id, -2)) {
            $id = substr($id, 0, strlen($id) - 2);
        }
        $id = str_replace('][', '-', $id);
        $id = str_replace(array(']', '['), '-', $id);
        $id = trim($id, '-');

        return $id;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttribute($attr, $value)
    {
        $this->attributes[$attr] = $value;
    }

    public function getAttribute($attr)
    {
        if (isset($this->attributes[$attr])) {
            return $this->attributes[$attr];
        }
        return null;
    }

    public function setMessages($messages)
    {
        if (!is_array($messages)) {
            $messages = (array) $messages;
        }
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set array to which element belongs
     *
     */
    public function setBelongsTo($array, $flg = false)
    {
        if (!empty($array)) {
            $this->_belongsTo = $array;
        }
        if ($flg) {
            $this->_belongsTo = null;
        }

        return $this;
    }

    /**
     * Return array name to which element belongs
     *
     * @return string
     */
    public function getBelongsTo()
    {
        return $this->_belongsTo;
    }

    public function isForm()
    {
        return false;
    }

    public function setRequired($isRequired)
    {
        $this->_isRequired = $isRequired;
    }

    public function isRequired()
    {
        return $this->_isRequired;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function addValidator($validator, $message = null)
    {
        $class = $validator;
        if(is_object($validator)) {
            $class = get_class($validator);
        }
        $arrName = explode('\\', $class);
        $name = $arrName[count($arrName) - 1];
        $this->_validators[$name] = $validator;
        if ($message) {
            $this->setMessagesRules($validator, $message);
        }
    }

    public function setMessagesRules($validator, $message)
    {
        $rules = explode(':', $validator);
        $messName = implode('.', [$this->getName(), $rules[0]]);
        $this->_messagesRules[$messName] = $message;
    }

    public function getMessagesRules()
    {
        return $this->_messagesRules;
    }

    public function getValidators()
    {
        $validators = $this->_validators;
        if ($this->isRequired()) {
            array_unshift($validators, 'required');
        }
        if ($this->registerInArrayValidator()) {
            if (!$this->getValidator('InArray')) {
                $validators['InArray'] = new \App\Rules\InArray(array_keys($this->getValueOptions()));
            }
            
        }
        // dd($validators);
        return $validators;
    }

    public function getValidator($name)
    {
        if (isset($this->_validators[$name])) {
            return $this->_validators[$name];
        }
        return null;
    }

    public function registerInArrayValidator() {
        return $this->_registerInArrayValidator;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function isValid($checkError = true)
    {
        $isValib = true;
        $data = [
            $this->getName() => $this->getValue()
        ];

        $validation = Validator::make($data, $this->getRules(), $this->getMessagesRules());
        $messages = $validation->errors()->getMessages();
        foreach ($messages as $name => $errors) {
            $this->setMessages($errors);
        }
        if ($checkError) {
            $this->checkErrors();
        }
        $isValib = empty($messages);
        return $isValib;
    }

    public function checkErrors()
    {
        $class = $this->getAttribute('class');
        if (!$class) {
            $class = array();
        }
        $class = (array)$class;
        if (!in_array('is-error', $class)) {
            $class[] = 'is-error';
        }
        $this->setAttribute('class', $class);
    }

    public function getRules()
    {
        return [
            $this->getName() => $this->_validators
        ];
    }

    public function hasErrors()
    {
        return count($this->getMessages()) > 0;
    }

    public function setAllowEmpty($allowEmpty)
    {
        $this->_allowEmpty = $allowEmpty;
    }

    public function getAllowEmpty()
    {
        return $this->_allowEmpty;
    }

    public function setValidator($validators)
    {
        $this->_validators = $validators;
    }

    public function removeValidator($name)
    {
        if (isset($this->_validators[$name])) {
            unset($this->_validators[$name]);
        } else {
            foreach ($this->_validators as $key => $validator) {
                $validatorName = str_replace('App\Rules\\', '', get_class($validator));
                if ($name == $validatorName) {
                    unset($this->_validators[$key]);
                    break;
                }
            }
        }

        return $this;
    }

    public function setValidRequired($isRequired)
    {
        $this->_isValidRequired = $isRequired;
    }

    public function isValidRequired()
    {
        return $this->_isValidRequired;
    }

    public function setregisterInArrayValidator($registerInArrayValidator)
    {
        $this->_registerInArrayValidator = $registerInArrayValidator;
    }
}
