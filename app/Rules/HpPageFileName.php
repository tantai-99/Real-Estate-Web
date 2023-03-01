<?php
namespace App\Rules;
use Library\Custom\Publish\Render\AbstractRender;
use App\Repositories\HpPage\HpPageRepository;

class HpPageFileName extends CustomRule {

	const INVALID = 'invalid';
	const IS_NUMERIC = 'is_numeric';
	const PREDEFINED = 'predefined';
	const INVALID_FORMAT = 'invalid_format';
    const INVALID_ARTICLE = 'invalid_article';

	protected $_table;
	protected $_hpId = 0;
	protected $_pageId = null;
    protected $_pageCategoryCode = null;
    protected $_pageTypeCode = null;

	public function __construct($options = array()) {
		// if ($options instanceof Zend_Config) {
		// 	$options = $options->toArray();
		// } else 
		if (!is_array($options) || func_num_args() > 1) {
			$args = func_get_args();
			$options = array();
			if (isset($args[0])) $options['table'] = $args[0];
			if (isset($args[1])) $options['hp_id'] = $args[1];
			if (isset($args[2])) $options['page_id'] = $args[2];
            if (isset($args[3])) $options['page_category_code'] = $args[3];
            if (isset($args[4])) $options['page_type_code'] = $args[4];
		}

		if (isset($options['table'])) {
			$this->setTable($options['table']);
		}
		if (isset($options['hp_id'])) {
			$this->setHpId($options['hp_id']);
		}
		if (isset($options['page_id'])) {
			$this->setPageId($options['page_id']);
        }
        if (isset($options['page_category_code'])) {
			$this->setPageCategoryCode($options['page_category_code']);
        }
        if (isset($options['page_type_code'])) {
			$this->setPageTypeCode($options['page_type_code']);
		}

		$this->init();
	}

	public function init() {

		$this->setPredefined();
	}

	public function setTable($table) {
		$this->_table = $table;
		return $this;
	}

	public function getTable() {
		return $this->_table;
	}

	public function setHpId($hpId) {
		$this->_hpId = $hpId;
		return $this;
	}

	public function getHpId() {
		return $this->_hpId;
	}

	public function setPageId($pageId) {
		$this->_pageId = $pageId;
		return $this;
	}

	public function getRow() {
		return $this->_row;
	}
    
    public function setPageCategoryCode($categoryCode) {
        $this->_pageCategoryCode = $categoryCode;
		return $this;
    }

    public function getPageCategoryCode() {
        return $this->_pageCategoryCode;
    }

    public function setPageTypeCode($typeCode) {
        $this->_pageTypeCode = $typeCode;
		return $this;
    }

    public function getPageTypeCode() {
        return $this->_pageTypeCode;
    }

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array(
			self::INVALID => "ほかのページと重複しています。",
			self::IS_NUMERIC => "英字を含めて入力してください。",
			self::INVALID_FORMAT => "半角英数字、「-」で入力してください。",
			self::PREDEFINED => "このページ名は使用できません。",
            self::INVALID_ARTICLE => "このページ名は使用できません。",
	);

	protected $predefined;

	private function setPredefined() {

		require_once(storage_path('data/publish/script/Search.php'));

		$this->predefined = array_merge(

				// お問い合わせ
				AbstractRender::getContactFileList(),

				/*
				 * 物件検索
				 * 下記が使用不可
				 * - urlの第一階層で使用する単語
				 * - view/{$device} 直下で使用するディレクトリ名
				 *
				 * @todo 定義場所整理する
				 */
				// {$物件種目}
				\Search::reserved_word_all(),
				[
					// お気に入り、最近見た物件
					'personal',
					// 物件検索 お問い合わせ
					'inquiry',
					// 情報の見方
					'howtoinfo',
					// api
					'api',
				]

		);

		$this->predefined[] = 'sitemap';
		$this->predefined[] = '404notfound';
		$this->predefined[] = 'pc';
		$this->predefined[] = 'sp';
		$this->predefined[] = 'images';
		$this->predefined[] = 'files';
		$this->predefined[] = 'chintai';
		$this->predefined[] = 'kasi-tenpo';
		$this->predefined[] = 'kasi-office';
		$this->predefined[] = 'parking';
		$this->predefined[] = 'kasi-tochi';
		$this->predefined[] = 'kasi-other';
		$this->predefined[] = 'mansion';
		$this->predefined[] = 'kodate';
		$this->predefined[] = 'uri-tochi';
		$this->predefined[] = 'uri-tenpo';
		$this->predefined[] = 'uri-office';
		$this->predefined[] = 'uri-other';
        $this->predefined[] = 'chintai-jigyo-1';
        $this->predefined[] = 'chintai-jigyo-2';
        $this->predefined[] = 'chintai-jigyo-3';
        $this->predefined[] = 'baibai-kyoju-1';
        $this->predefined[] = 'baibai-kyoju-2';
        $this->predefined[] = 'baibai-jigyo-1';
		$this->predefined[] = 'baibai-jigyo-2';
		$this->predefined[] = 'all';
        $this->predefined[] = 'top';
        $this->predefined[] = 'file2s';
        if (!$this->isArticlePageTop()) {
            $this->predefined[] = 'article';
        }
	}

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
        if ($this->isArticlePage() && $this->_table->inUseFileNameArticle($value, $this->_pageTypeCode)) {
            $this->invokableRuleError($fail, self::INVALID_ARTICLE);
			return false;
        }

		if ($this->_table->inUseFileNameWithoutNew($value, $this->_hpId, $this->_pageId, $this->_pageCategoryCode)) {
			$this->invokableRuleError($fail, self::INVALID);
			return false;
		}

		if (in_array(strtolower($value), $this->predefined)) {
			$this->invokableRuleError($fail, self::PREDEFINED);
			return false;
		}

		// 特集
		if (preg_match('/^sp-/', strtolower($value))) {
			$this->invokableRuleError($fail, self::PREDEFINED);
			return false;
		}

		$regex = '/^[0-9a-zA-Z\-]*$/';
		if (!preg_match($regex, $value, $matches)) {
			$this->invokableRuleError($fail, self::INVALID_FORMAT);
			return false;
		}

		if (!preg_match('/[a-zA-Z]/', $value)) {
			$this->invokableRuleError($fail, self::IS_NUMERIC);
			return false;
		}

		return true;
    }
    
    public function isArticlePageTop() {
        return $this->getPageCategoryCode() == HpPageRepository::CATEGORY_TOP_ARTICLE;
    }

    public function isArticlePage() {
        return in_array($this->getPageCategoryCode(), $this->_table->getCategoryCodeArticle());
    }
}
