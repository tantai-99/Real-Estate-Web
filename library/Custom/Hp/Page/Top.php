<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use Library\Custom\User\Cms;
use Library\Custom\Hp\Page\SectionParts\MainImage;
use App\Repositories\HpTopImage\HpTopImageRepositoryInterface;
use Illuminate\Support\Facades\App;

class Top extends HpPage {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_INFO_LIST,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_INFO_LIST,
			)
		),
	);

	public function initContents() {
		$options = array('hp' => $this->getHp(), 'page'=>$this->getRow());
        if (!Cms::getInstance()->checkHasTopOriginal()) {
            $this->form->addSubForm(new MainImage($options), 'main_image');
        }
	}

	/**
	 * (non-PHPdoc)
	 * @see Library\Custom\Hp\Page::_decoratePresetParts()
	 */
	protected function _decoratePresetParts($parts, $type) {
		if ($type == HpMainPartsRepository::PARTS_INFO_LIST) {
			$parts->getElement('heading')->setValue('NEWS');
		}
	}

	protected function _load() {
		parent::_load();

		// メイン画像のロード
        if (!Cms::getInstance()->checkHasTopOriginal()) {
			$table = App::make(HpTopImageRepositoryInterface::class);
			$data = [];
			$dataRow = $table->fetchAll(array(['page_id', $this->_row->id], ['hp_id', $this->_hp->id]), array('sort'));
			foreach ($dataRow as $key => $value) {
				$data[$value->sort] = $value->toArray();
			}
			$this->form->getSubForm('main_image')->setData($data);
        }
	}
}