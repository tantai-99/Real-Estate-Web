<?php
namespace Library\Custom\Kaiin\RemarcKeiyaku;
use Library\Custom\Kaiin\AbstractParams;

class RemarcKeiyakuParams extends AbstractParams
{

    private $kaiinNo;

    public function setKaiinNo($kaiin_no)
    {
        $this->kaiinNo = $kaiin_no;
    }
    
    public function getKaiinNo()
    {
        return $this->kaiinNo;
    }
}