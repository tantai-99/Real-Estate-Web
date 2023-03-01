<?php

namespace App\Rules;

use Library\Custom\Util;
use App\Repositories\HpPage\HpPageRepository;

class ParentHpPageId extends HpPageId
{

	const INVALID_CHILD = 'invalid_child';
	const INVALID_LEVEL = 'invalid_level';

	public function init()
	{
		$this->_messageTemplates[self::INVALID_CHILD] = "階層に誤りがあります。";
		$this->_messageTemplates[self::INVALID_LEVEL] = "階層の上限を超えています。";
	}


	/**
	 * @param  string $value
	 * @return boolean
	 */
	public function isValid($value, $context = null)
	{
		$isValid = parent::isValid($value, $context);

		if (!$isValid) {
			return $isValid;
		}

		if (!$context || !isset($context['page_type_code'])) {
			// 自身のタイプ不明の場合はエラー
			$this->_error(self::INVALID_CHILD);
			return false;
		}
		$type = (int)$context['page_type_code'];

		// 階層外の場合
		if (Util::isEmpty($value)) {
			$notInMenuTypes = $this->_table->getNotInMenuTypeList();
			if (!in_array($type, $gMenuTypes, true)) {
				$this->_error(self::INVALID_CHILD);
				return false;
			}
			return true;
		}
		// グロナビの場合
		else if ($value == 0) {
			$gMenuTypes = $this->_table->getGlobalMenuTypeList();
			if (!in_array($type, $gMenuTypes, true)) {
				$this->_error(self::INVALID_CHILD);
				return false;
			}
			return true;
		}
		// TOPはグロナビ固定
		else if ($type === HpPageRepository::TYPE_TOP) {
			$this->_error(self::INVALID);
			return false;
		}

		// 階層チェック
		$maxLevel = HpPageRepository::MAX_LEVEL;
		if (in_array($type, $this->_table->getHasDetailPageTypeList(), true)) {
			$maxLevel--;
		}
		if ($this->_row->level >= $maxLevel) {
			$this->_error(self::INVALID_LEVEL);
			return false;
		}
		if (in_array($this->_row->page_category_code, $this->_table->getCategoryCodeArticle())) {
			$childTypes = $this->_table->getChildTypesUsefulEstateByType($this->_row->page_type_code);
		} else {
			$childTypes = $this->_table->getChildTypesByType($this->_row->page_type_code);
		}

		if (!in_array($type, $childTypes, true)) {
			$this->_error(self::INVALID_CHILD);
			return false;
		}

		return true;
	}
}
