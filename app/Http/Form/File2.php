<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Illuminate\Support\Facades\App;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;
use App\Rules\StringLength;
use App\Rules\File2Content;

class File2 extends Form {
	
	public function __construct()
	{	
		parent::__construct();

		$hp = getInstanceUser('cms')->getCurrentHp();
		
		$element = new Element\Hidden( 'hp_file2_content_id' ) ;
		$element->setLabel( 'ファイル' ) ;
		$element->setRequired( true ) ;
		$element->setAttributes([
			'class' => array('upload-file-id'),
			'data-file-type' => 'file2',
			'data-upload-to' => '/api-upload/hp-file2',
			'data-view' => '/file/hp-file2',
		]);
		$element->addValidator(new File2Content(App::make(HpFile2ContentRepositoryInterface::class), $hp->id));
		$this->add( $element ) ;
		
		$element = new Element\Text( 'title' ) ;
		$element->setLabel( 'ファイルタイトル' );
		$element->setAttributes([
			'class' => array('watch-input-count'),
			'maxlength' => 30,
		]);
		$element->addValidator(new StringLength(array('max' => 30)));
		$this->add( $element ) ;
		
		$element = new Element\Select( 'category_id' ) ;
		$element->setLabel( 'ファイルカテゴリ' );
		$element->setValueOptions( array( 0 => '選択してください' ) ) ;
		$this->add( $element ) ;
	}
	
	public function getMessages()
	{
		$messages = array() ;
	
		foreach ( $this->getElements() as $name => $element ) {
			if ( !$element->hasErrors() ) {
				continue ;
			}

			$messages[ $name ] = $this->getGroupErrors( array( $name ) ) ;
		}
	
		return $messages ;
	}
	
}