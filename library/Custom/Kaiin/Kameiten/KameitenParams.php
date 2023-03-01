<?php
namespace Library\Custom\Kaiin\Kameiten;
use Library\Custom\Kaiin\AbstractParams;

class KameitenParams extends AbstractParams
{

    // APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。

    // APIパラメータに使用されない
    // 変則的に使用される変数はprivateで定義。
    protected $Orders = 'bukkenShogoKana';
    protected $BukkenShogo;
    protected $DaihyoTel;
    protected $page;
    protected $perpage;

    public function setBukkenShogo($BukkenShogo)
    {
        $this->BukkenShogo = urlencode($BukkenShogo);
    }
    
    public function getBukkenShogo()
    {
        return $this->BukkenShogo;
    }

    public function setDaihyoTel($DaihyoTel)
    {
        $this->DaihyoTel = urlencode($DaihyoTel);
    }
    
    public function getDaihyoTel()
    {
        return $this->DaihyoTel;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }
    
    public function getPage()
    {
        return $this->page;
    }

    public function setPerpage($perpage)
    {
        $this->perpage = $perpage;
    }
    
    public function getPerpage()
    {
        return $this->perpage;
    }


}