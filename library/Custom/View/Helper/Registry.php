<?php
namespace Library\Custom\View\Helper;
/**
 * Zend_View内でのみ有効なグローバル変数領域
 */
class Registry extends  HelperAbstract
{
    /**
     * @var array
     */
    private $_data;

    /**
     * @param string $key
     * @param mixed $value
     * @return $this|null
     */
    public function registry($key = null, $value = null)
    {
        if (is_null($key)) {
            return $this;
        }

        if (is_null($value)) {
            return $this->get($key);
        }

        return $this->set($key, $value);
    }

    public function set($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }
}