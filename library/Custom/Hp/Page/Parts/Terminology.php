<?php
namespace Library\Custom\Hp\Page\Parts;

use Library\Custom\Hp\Page\Parts\AbstractParts\HasElement;
use  Library\Custom\Hp\Page\Parts\Element;

class Terminology extends HasElement {

	protected $_title = '用語集';
	protected $_template = 'terminology';

	protected $_has_heading = false;

	protected $_is_unique = true;

	protected $_freeTypes = array(
			'terminology'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'terminology') {
			$element = new Element\Terminology();
		}
		return $element;
	}

	protected $_syllabary = array(
			'あ' => 'あいうえおぁぃぅぇぉ',
			'か' => 'かきくけこがぎぐげご',
			'さ' => 'さしすせそざじずぜぞ',
			'た' => 'たちつてとだぢづでどっ',
			'な' => 'なにぬねの',
			'は' => 'はひふへほばびぶべぼぱぴぷぺぽ',
			'ま' => 'まみむめも',
			'や' => 'やゐゆゑよゃゅょ',
			'ら' => 'らりるれろ',
			'わ' => 'わをんゎ',
	);

	public function getElementsBySyllabary() {
		$forms = $this->getSubForm('elements')->getSubForms();
		usort($forms, array($this, '_sortBySyllabary'));

		$ret = array();
		foreach ($this->_syllabary as $row => $strs) {
			$ret[$row] = array();
		}

		foreach ($forms as $no => $form) {
			$kana = (string) $form->getElement('kana')->getValue();
			$key = '';
			if ($kana) {
				$first = mb_substr($kana, 0, 1);
				foreach ($this->_syllabary as $row => $strs) {
					if (false !== strpos($strs, $first)) {
						$key = $row;
						break;
					}
				}
			}
			else {
				$key = 'わ';
			}

			if (isset($ret[$key])) {
				$ret[$key][] = $form;
			}
		}

		return $ret;
	}

	/**
	 *
	 * @param Library\Custom\Hp\Page\Parts\Element\Terminology $a
	 * @param Library\Custom\Hp\Page\Parts\Element\Terminology $b
	 */
	protected function _sortBySyllabary($a, $b) {
		$akana = (string) $a->getElement('kana')->getValue();
		$bkana = (string) $b->getElement('kana')->getValue();

		if ($akana == $bkana) {
			return 0;
		}

		if ($akana < $bkana) {
			return -1;
		}

		return 1;
	}
}