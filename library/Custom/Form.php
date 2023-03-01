<?php
namespace Library\Custom;

use Library\Custom\Form\Element;
use Validator;

class Form {

    /** @var array */
    protected $elements = [];

    protected $fieldsets = [];

    protected $_attribs = [];
    /** @var array */
    protected $_columnMap = [];

    protected $name; 

    protected $validation;

    protected $validator;

    protected $_elementBelongsTo;

    protected $_name;

    protected $_messages = [];

    protected $_title;

    protected $_is_unique = false;

    protected $_is_preset = false;


    public function __construct($options = [])
    {
        $this->setOptionsCustom($options);
        $this->init();
    }

    public function init() {}

    public function __get($name) {
        if ($this->getElement($name)) {
            return $this->getElement($name);
        }
        if ($this->getSubForm($name)) {
            return $this->getSubForm($name);
        }
        
        throw new \Exception('not found element or form');
    }

    public function getElement($name)
    {
        if (isset($this->elements[$name])) {
            return $this->elements[$name];
        }
        return null;
    }

    public function setName($name)
    {
        if (preg_match('/\[(.+)\]/', $name, $matches)){
            $name = array_pop($matches);
        }
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setData($data)
    {
        $data = $this->_dissolveArrayValue($data, $this->getElementsBelongTo());
        foreach ($this->getSubForms() as $key => $form) {
            $form->setData($data);
            if (get_class($form) == 'Library\Custom\Hp\Page\Parts\EstateKoma') {
                $form->setDefaults($data);
            }
        }

        if (!empty($data) && is_array($data)) {
            foreach ($this->_columnMap as $key => $value) {
                if (!isset($data[$value])) continue;
                $data[$key] = $data[$value];
                if ($key != $value) {
                    unset($data[$value]);
                }
            }
            foreach ($data as $name => $value) {
                if (isset($this->elements[$name])) {
                    $this->elements[$name]->setValue($value);
                }
            }
        }
    }

    public function getDatas()
    {
        $data = [];
        foreach ($this->getElements() as $key => $element) {
            $data[$key] = $element->getValue();
        }
        return $data;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function setOptionsCustom($options = [])
    {
        foreach ($options as $key => $value) {
            $normalized = ucfirst($key);

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function add($element, $name = '')
    {
        if ($element->isForm()) {
            if ($name == '') {
                $this->fieldsets[] = $element;
            } else {
                $element->setName($name);
                $this->fieldsets[$name] = $element;
            }
        } else {
            if ($name == '') {
                $name = $element->getName();
            }
            $this->elements[$name] = $element;
        }
    }

    public function addSubForm($element, $name)
    {
        $this->add($element, $name);
        $elementBelongsTo = $this->getElementBelongsTo();
        $element->setElementsBelongsTo($name, $elementBelongsTo);
    }

    public function getSubForms()
    {
        return $this->fieldsets;
    }

    public function getSubForm($key)
    {
        if (isset($this->fieldsets[$key])) {
            return $this->fieldsets[$key];
        }
        return null;
    }

    public function clearAttribs()
    {
        $this->_attribs = array();
        return $this;
    }

    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;
        return $this;
    }

    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    public function getElementBelongsTo()
    {
        return $this->_elementBelongsTo;
    }

    public function setElementsBelongsTo($name, $elementsBelongsTo)
    {
        if (is_null($elementsBelongsTo)) {
            $this->_elementBelongsTo = $name;
        } else {
            $this->_elementBelongsTo = $elementsBelongsTo . '[' . $name . ']';
        }

        foreach ($this->getElements() as $name => $element) {
            $element->setBelongsTo($this->_elementBelongsTo);
        }
        return $this;
    }
    /**
     * @param string $name
     */
    public function form($name, $echo = true)
    {
        $element = $this->getElement($name);
        $type = $this->getTypeElement($element);
        if ($type == 'radio') {
            return $this->simpleRadio($name, $echo);
        }
        if ($type == 'select') {
            return $this->simpleSelect($name);
        }
        if ($type == 'checkbox' || $type == 'multicheckbox') {
            return $this->simpleCheckbox($name, $echo);
        }
        if (!$echo) {
            return $element;
        }

        if ($type == 'textarea' || $type == 'wysiwyg') {
            echo '<textarea ' . $this->_simpleAttrs($element) . '>' . h($element->getValue()) . '</textarea>';
            return;
        }
        echo $this->_simpleInput($element, $type);
    }

    public function simpleHidden($name)
    {
        $element = $this->getElement($name);
        echo $this->_simpleInput($element, 'hidden');
    }

    public function simpleSelect($name)
    {
        $element = $this->getElement($name);
        $html =  '<select ' . $this->_simpleAttrs($element) . '>';
        $options = $element->getValueOptions();
        $selected = $element->getValue();
        reset($options);
        foreach ($options as $value => $label) {
            $html .= '<option value="' . h($value) . '"' . ($selected == (string) $value ? ' selected="selected"' : '') . '>' . h($label) . '</option>';
        }
        $html .= '</select>';

        echo $html;
    }

    public function simpleText($name)
    {
        $element = $this->getElement($name);
        if ($element instanceof Element\Textarea || $element instanceof Element\Wysiwyg) {
            echo '<textarea ' . $this->_simpleAttrs($element) . '>' . h($element->getValue()) . '</textarea>';
            return;
        }
        echo $this->_simpleInput($element, 'text');
    }

    public function simpleSelectData($name)
    {
        $element = $this->getElement($name);
        $options = $element->getValueOptions();
        $options['selected'] = $element->getValue();
        reset($options);
        return $options;
    }

    public function getSelectPageTitle($name)
    {
        $element = $this->getElement($name);
        $options = $element->getValueOptions();
        $selected = $element->getValue();
        reset($options);
        foreach ($options as $value => $label) {
            if ($selected == $value) {
                $title = h($label);
                if ($title == '選択してください') {
                    $title = '';
                } else {
                    $title = '選択中ページ：' . $title;
                }
                return $title;
            } else {
                $title = '';
            }
        }
        return $title;
    }

    public function simpleRadio($name, $echo = true)
    {
        $element = $this->getElement($name);
        $html =  '';
        $options = $element->getValueOptions();
        $selected = $element->getValue();
        reset($options);
        foreach ($options as $value => $label) {
            $html .= '<label><input type="radio" ' . $this->_simpleAttrs($element) . 'id="' . $element->getId() . '-' . $value . '" value="' . h($value) . '"' . (!is_null($selected) && $selected == $value ? ' checked="checked"' : '') . '>' . h($label) . '</label>'. $element->getSeparator();
        }
        if (!$echo) {
            return $html;
        }
        echo $html;
    }

    public function simpleCheckbox($name, $echo = true)
    {
        $element = $this->getElement($name);
        $valueOption = $element->getValueOptions();
        $selected = $element->getValue();
        if(!is_array($selected)){
            $selected = (array) $selected;
        }
        if (count($valueOption) == 0) {
            if ($element->isChecked()) {
                if (!$element->getValue()) {
                    $element->setValue(1);
                }
            }
            $selected = $element->getValue() ? $element->getValue() : 0;
            $element->setIsArray();
            $html = '<input type="hidden" name="' . $element->getFullName() . '" value="0"><input type="checkbox" ' . $this->_simpleAttrs($element) . 'id="' . $element->getId() . '"  value="1"' . (($selected == 1 || $element->isChecked()) ? ' checked="checked"' : '') . '>';
            if (!$echo) {
                return $html;
            }
            echo $html;
        }

        $html = '';
        foreach ($valueOption as $key => $val) {
            $html .= '<label><input type="checkbox" ' . $this->_simpleAttrs($element) . 'id="' . $element->getId() . '-' . $key . '"  value="' . $key . '"' . (isset($selected) && in_array($key, $selected) ? ' checked="checked"' : '') . '>' . $val . '</label>'. $element->getSeparator();
        }
        if (!$echo) {
            return $html;
        }
        echo $html;
    }

    public function _simpleInput($element, $type)
    {
        return '<input type="' . $type . '" ' . $this->_simpleAttrs($element) . ' value="' . h($element->getValue()) . '">';
    }

    public function _simpleAttrs($element)
    {
        $id = 'id="' . $element->getId();
        if ($element->getType() == 'checkbox' || $element->getType() == 'radio'  || $element->getType() == 'multiCheckbox') {
            $id = '';
        }
        if ($element->getAttribute('id')) {
            $id = 'id="' .$element->getAttribute('id'). '"';
        }
        $attrs = $id . '" class="' . implode(' ', (array)$element->getAttribute('class')) . '" name="' . $element->getFullName() . '"';
        foreach ($element->getAttributes() as $attr => $value) {
            if (in_array($attr, ['id', 'class', 'name'])) continue;
            $attrs .= $attr . '="' . $value . '"';
        }
        return $attrs;
    }

    public function getTypeElement($element)
    {
        return strtolower(str_replace('Library\Custom\Form\Element\\', '', get_class($element)));
    }

    public function getGroupErrors($elements = array()) {
		if (!$elements) {
			$elements = $this->getElements();
		}
		$messages = array();
		foreach ($elements as $name => $element) {
			if (!($element instanceof Element)) {
				$name = $element;
				$element = $this->getElement($name);
			}
			foreach ($element->getMessages() as $key => $message) {
				$messages[$key] = $message;
			}
		}
		return $messages;
	}

    public function checkErrors()
    {
        foreach ($this->getElements() as $element) {
            if ($element instanceof \Library\Custom\Form) {
                $element->checkErrors();
            } else if (!empty($element->getMessages())) {
                $class = $element->getAttribute('class');
                if (!$class) {
                    $class = array();
                }
                $class = (array)$class;
                if (!in_array('is-error', $class)) {
                    $class[] = 'is-error';
                }
                $element->setAttribute('class', $class);
            }
        }
    }

    public function isValid($params, $checkError = true)
    {   
        $isValib = true;
        $data = $this->getDatas();
        $validation = Validator::make($data, $this->getRules());
        $this->_messages = $validation->errors()->getMessages();
        foreach ($this->_messages as $name => $errors) {
            $this->getElement($name)->setMessages($errors);
        }
        if ($checkError) {
            $this->checkErrors();
        }
        $isValib = !(isset($this->_messages) && count($this->_messages));
        foreach ($this->getSubForms() as $key => $form) {
            $isValib = $form->isValid($params) && $isValib;
        }
        return $isValib;
    }
    

    public function getMessages()
    {
        if (empty($this->_messages)) {
            foreach($this->getElements() as $name=>$element) {
                if ($element->hasErrors()) {
                    $this->_messages[$name] = $element->getMessages();
                }
            }
        }
        return $this->_messages;
    }

    public function setMessages($messages = array())
    {
        $this->_messages = $messages;
    }

    public function isForm()
    {
        return true;
    }

    protected function _dissolveArrayValue($value, $arrayPath)
    {
        // As long as we have more levels
        while ($arrayPos = strpos($arrayPath, '[')) {
            // Get the next key in the path
            $arrayKey = trim(substr($arrayPath, 0, $arrayPos), ']');

            // Set the potentially final value or the next search point in the array
            if (isset($value[$arrayKey])) {
                $value = $value[$arrayKey];
            }

            // Set the next search point in the path
            $arrayPath = trim(substr($arrayPath, $arrayPos + 1), ']');
        }

        if (isset($value[$arrayPath])) {
            $value = $value[$arrayPath];
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function getRules()
    {
        $rules = [];
        foreach ($this->getElements() as $name => $element) {
            $rule = $element->getValidators();
            if (count($rule) > 0) {
                $rules[$name] = $rule;
            }
        }
        return $rules;
    }

    public function removeElement($name)
    {
        if (isset($this->elements[$name])) {
            unset($this->elements[$name]);
        }
    }

    public function getValue($name)
    {
        $element = $this->getElement($name);
        if ($element) {
            return $element->getValue();
        }
        return null;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setEmptyForm() {
        foreach($this->getElements() as $name=>$element) {
            $element->setValue(null);
        }
    }
    
    public function hasErrors()
    {
        if(!empty($this->messages))
        {
            return true;
        }
        return false;
    }

    public function getValues() {
        $values = array();
        foreach ($this->getElements() as $key => $element) {
            $merge = array();
            $merge = $this->_attachToArray($element->getValue(), $key);
            $values = $this->_array_replace_recursive($values, $merge);
        }
        /** @var Library\Custom\Form $subForm */
        foreach ($this->getSubForms() as $key => $subForm) {
            $merge = array();
            $merge = $this->_attachToArray($subForm->getValues(), $subForm->getElementBelongsTo());
            $values = $this->_array_replace_recursive($values, $merge);
        }
        return $values;
    }

    protected function _attachToArray($value, $arrayPath)
    {
        // As long as we have more levels
        while ($arrayPos = strrpos($arrayPath, '[')) {
            // Get the next key in the path
            $arrayKey = trim(substr($arrayPath, $arrayPos + 1), ']');

            // Attach
            $value = array($arrayKey => $value);

            // Set the next search point in the path
            $arrayPath = trim(substr($arrayPath, 0, $arrayPos), ']');
        }

        $value = array($arrayPath => $value);

        return $value;
    }

    protected function _array_replace_recursive(array $into)
    {
        $fromArrays = array_slice(func_get_args(),1);

        foreach ($fromArrays as $from) {
            foreach ($from as $key => $value) {
                if (is_array($value)) {
                    if (!isset($into[$key])) {
                        $into[$key] = array();
                    }
                    $into[$key] = $this->_array_replace_recursive($into[$key], $from[$key]);
                } else {
                    $into[$key] = $value;
                }
            }
        }
        return $into;
    }

    public function getMessagesById() {
        $messages = array();
        foreach ($this->getElements() as $name => $element) {
            if ($_messages = $element->getMessages()) {
                $messages[$element->getId()] = $_messages;
            }
        }
        foreach ($this->getSubForms() as $form) {
            $messages = array_merge($messages, $form->getMessagesById());
        }
        
        return $messages;
    }

    public function setBelongTo($belongTo)
    {
        foreach ($this->getElements() as $name => $element) {
            if ($this->getElementBelongsTo() != 'tdk' && strpos($this->getElementBelongsTo(), 'main_image') === false) {
                $element->setBelongsTo($belongTo);
            }
        }
        return $this;
    }

    public function setBelongToRecursive($belongTo = null) {
        foreach ($this->getSubForms() as $form) {
            if (!is_null($form->getName())) {
                $_belongTo = $belongTo ? $belongTo . '[' . $form->getName() . ']' : $form->getName();
            } else {
                $elementBelongsTo = null;
                if ($form->getElementBelongsTo() != 'form') {
                    $elementBelongsTo = $form->getElementBelongsTo();
                }
                $_belongTo = $belongTo ? $belongTo . $elementBelongsTo : $elementBelongsTo;
            }
            $form->setBelongTo($_belongTo);
            $form->setBelongToRecursive($_belongTo);
        }
        return $this;
    }
    
    public function setIsUnique($isUnique) {
		$this->_is_unique = $isUnique;
		return $this;
	}

    public function isUnique() {
		return $this->_is_unique;
	}

    public function setIsPreset($isPreset) {
		$this->_is_preset = $isPreset;
		return $this;
	}

	public function isPreset() {
		return $this->_is_preset;
	}

    public function getElementsBelongTo() {
        foreach ($this->getElements() as $key => $element) {
            return $element->getBelongsTo();
        }
        return null;
    }
}
