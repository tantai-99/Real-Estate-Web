<?php
namespace Library\Custom\Model\Lists;

class InformationMainImageSlideShow extends ListAbstract {

    const EFFECT_HORIZONTAL     = 1;
    const EFFECT_VERTICAL       = 2;
    const EFFECT_FADE_IN_OUT    = 3;
    // const EFFECT_CAROUSEL       = 4;
    const EFFECT_CIRCLES        = 5;
    const EFFECT_HIDEBARSL      = 6;
    const EFFECT_BLIND          = 7;
    const EFFECT_BLINDHEIGHT    = 8;
    const EFFECT_SWAPBLOCKS     = 9;
    const EFFECT_CUBESPREAD     = 10;
    const EFFECT_CUBERANDOM     = 11;
    const EFFECT_CUBESHOW       = 12;
    const EFFECT_RANDOM         = 13;

    const SPEED_FAST            = 1;
    const SPEED_NORMAL          = 2;
    const SPEED_SLOW            = 3;

    const NAVIGATION_NONE       = 1;
    const NAVIGATION_CIRCLE     = 2;
    const NAVIGATION_BAR        = 3;
    const NAVIGATION_NUMBER     = 4;
    const NAVIGATION_THUMBNAIL  = 5;

    const LIB_SLICK             = 1;
    const LIB_SKITTER           = 2;

    const PC_WIDTH              = 720;
    const PC_HEIGHT             = 320;

    const SP_WIDTH              = 720;
    const SP_HEIGHT             = 320;

    static public function getAminationsSkitter($effect=-1){
        $result =  [
            self::EFFECT_CIRCLES        => 'circles',
            self::EFFECT_HIDEBARSL      => 'hideBars',
            self::EFFECT_BLIND          => 'blind',
            self::EFFECT_BLINDHEIGHT    => 'blindHeight',
            self::EFFECT_SWAPBLOCKS     => 'swapBlocks',
            self::EFFECT_CUBESPREAD     => 'cubeSpread',
            self::EFFECT_CUBERANDOM     => 'cubeRandom',
            self::EFFECT_CUBESHOW       => 'cubeShow',
        ];
        if($effect==self::EFFECT_RANDOM){
            return $result;
        }
        if($effect==-1)
            return '';
        else
            return $result[$effect];
    }

    static public function getAminationsGif($effect){
        $result =  [
            self::EFFECT_HORIZONTAL     => 'horizontal',
            self::EFFECT_VERTICAL       => 'vertical',
            self::EFFECT_FADE_IN_OUT    => 'fade_in_out',
            // self::EFFECT_CAROUSEL       => 'carousel',
            self::EFFECT_CIRCLES        => 'circles',
            self::EFFECT_HIDEBARSL      => 'hidebars',
            self::EFFECT_BLIND          => 'blind',
            self::EFFECT_BLINDHEIGHT    => 'blindHeight',
            self::EFFECT_SWAPBLOCKS     => 'swapBlocks',
            self::EFFECT_CUBESPREAD     => 'cubeSpread',
            self::EFFECT_CUBERANDOM     => 'cubeRandom',
            self::EFFECT_CUBESHOW       => 'cubeShow',
            self::EFFECT_RANDOM  => 'skitter_random',
        ];
        return $result[$effect];
    }

    static public function getLibsJS($lib){
        switch($lib){
            case self::LIB_SLICK:
                return [];
            case self::LIB_SKITTER:
                return ['jquery.easing.js','skitter.min.js','skitter-run.js'];
        }
        return [];
    }

    static public function getLibsCSS($lib){
        switch($lib){
            case self::LIB_SLICK:
                return ['slick-custom.css'];
            case self::LIB_SKITTER:
                return ['skitter.css','skitter-custom.css'];
        }
        return [];
    }

    static public function getNamesEffect()
    {
        return [
            self::EFFECT_HORIZONTAL     => '横スライダー',
            self::EFFECT_VERTICAL       => '縦スライダー',
            self::EFFECT_FADE_IN_OUT    => 'フエードイン・フエードアウト',
            // self::EFFECT_CAROUSEL       => 'カルーセルスライダー',
            self::EFFECT_CIRCLES        => 'サークル',
            self::EFFECT_HIDEBARSL      => 'スライドバー1',
            self::EFFECT_BLIND          => 'スライドバー2',
            self::EFFECT_BLINDHEIGHT   => 'スライドバー3',
            self::EFFECT_SWAPBLOCKS     => 'クロス',
            self::EFFECT_CUBESPREAD     => 'キューブ1',
            self::EFFECT_CUBERANDOM     => 'キューブ2',
            self::EFFECT_CUBESHOW       => 'キューブ3',
            self::EFFECT_RANDOM         => 'ランダム',
        ];
    }

    static public function getLibsEffect(){
        return [
            self::EFFECT_HORIZONTAL     => self::LIB_SLICK  ,
            self::EFFECT_VERTICAL       => self::LIB_SLICK  ,
            self::EFFECT_FADE_IN_OUT    => self::LIB_SLICK  ,
            // self::EFFECT_CAROUSEL       => self::LIB_SLICK  ,
            self::EFFECT_CIRCLES        => self::LIB_SKITTER,
            self::EFFECT_HIDEBARSL      => self::LIB_SKITTER,
            self::EFFECT_BLIND          => self::LIB_SKITTER,
            self::EFFECT_BLINDHEIGHT   => self::LIB_SKITTER,
            self::EFFECT_SWAPBLOCKS     => self::LIB_SKITTER,
            self::EFFECT_CUBESPREAD     => self::LIB_SKITTER,
            self::EFFECT_CUBERANDOM     => self::LIB_SKITTER,
            self::EFFECT_CUBESHOW       => self::LIB_SKITTER,
            self::EFFECT_RANDOM         => self::LIB_SKITTER,
        ];
    }


    static public function getNamesSpeed()
    {
        return [
            self::SPEED_FAST            => 'はやめ',
            self::SPEED_NORMAL          => 'ふつう',
            self::SPEED_SLOW            => 'ゆっくり',
        ];
    }

    static public function getValuesSpeed()
    {
        return [
            self::SPEED_FAST            => 3000,
            self::SPEED_NORMAL          => 5000,
            self::SPEED_SLOW            => 7000,
        ];
    }

    static public function getNamesNavigation()
    {
        return [
            self::NAVIGATION_CIRCLE     => '丸（デフォルト設定）',
            self::NAVIGATION_BAR        => 'バー',
            self::NAVIGATION_NUMBER     => '数字',
            self::NAVIGATION_THUMBNAIL  => 'サムネイル（縮小画像）',
            self::NAVIGATION_NONE       => 'なし',
        ];
    }

    static public function getClassFrontNavigation()
    {
        return [
            self::NAVIGATION_CIRCLE     => 'slick-dots',
            self::NAVIGATION_BAR        => 'slick-dotr',
            self::NAVIGATION_NUMBER     => 'slick-dotnumber',
        ];
    }

    static public function getClassFrontSpeedBar($typeSpeed)
    {
        $class = '';
        switch ($typeSpeed) {
            case self::SPEED_FAST:
                $class = 'fast';
                break;
            case self::SPEED_NORMAL:
                $class = 'normal';
                break;
            case self::SPEED_SLOW:
                $class = 'slow';
                break;
        }
        return $class;
    }

    static public function getNameLib($lib)
    {
        $name = '';
        switch ($lib) {
            case self::LIB_SLICK:
                $name = 'slick';
                break;
            case self::LIB_SKITTER:
                $name = 'skitter';
                break;
        }
        return $name;
    }

    static public function getClassName($lib){
        $name = '';
        switch ($lib) {
            case self::LIB_SLICK:
                $name = 'slider single-item';
                break;
            case self::LIB_SKITTER:
                $name = 'skitter skitter-large';
                break;
        }
        return $name;
    }

    static public function getHtmlTag($lib){
        $name = [];
        switch ($lib) {
            case self::LIB_SLICK:
                $name = ['group'=>['begin'=>'','end'=>''],'item'=>['begin'=>'<div>','end'=>'</div>']];
                break;
            case self::LIB_SKITTER:
                $name = ['group'=>['begin'=>'<ul>','end'=>'</ul>'],'item'=>['begin'=>'<li>','end'=>'</li>']];
                break;
        }
        return $name;
    }
}