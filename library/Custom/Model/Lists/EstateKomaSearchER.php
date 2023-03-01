<?php
namespace Library\Custom\Model\Lists;

class EstateKomaSearchER extends ListAbstract
{
	const SIZE_SMALL		=	'small';
	
	const SIZE_MEDIUM		=	'middle';
	
	const SIZE_LARGE		=	'large';
	
	const WIDTH_NO_SMALL 	= 	149;
	
	const WIDTH_NO_MEDIUM 	= 	199;
	
	const WIDTH_NO_LARGE 	= 	229;

	const HEIGHT_NO_SMALL 	= 	160;

	const HEIGHT_NO_MEDIUM  = 	208;

    const HEIGHT_NO_LARGE 	= 	240;
    
    const HEIGHT_SP         =   118;

    const WIDTH_SP          =   '100%';

    const HEIGHT_NO_MAX     =   20;

	static public function getSize($size)
	{
		switch ($size) {
			case self::SIZE_SMALL:
				return [self::HEIGHT_NO_SMALL, self::WIDTH_NO_SMALL];
			case self::SIZE_MEDIUM:
				return [self::HEIGHT_NO_MEDIUM, self::WIDTH_NO_MEDIUM];
			case self::SIZE_LARGE:
				return [self::HEIGHT_NO_LARGE, self::WIDTH_NO_LARGE];
			default:
				return [self::HEIGHT_NO_SMALL, self::WIDTH_NO_SMALL];
		}
	}
    
    static public function getSizes(){
        return [self::SIZE_SMALL, self::SIZE_MEDIUM, self::SIZE_LARGE];
    }

    static public function setDefault(&$height, &$width, &$size) {
        if($height == 0) {
            $height = 1;
        }
        if($width == 0) {
            $width = 1;
        }
        if(!in_array($size, self::getSizes())) {
            $size = 'small';
        }
    }
}