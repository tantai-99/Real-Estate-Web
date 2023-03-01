<?php
namespace library\Custom\Assessment\Features;
use App\Repositories\HpTopImage\HpTopImageRepositoryInterface;
use Illuminate\Support\Facades\App;

class TopImages extends AbstractFeatures
{

    /**
     * TOP画像を設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->checkHasTopOriginal()) return true;
        
        $table = App::make(HpTopImageRepositoryInterface::class);
        $where=[['hp_id',$this->hp->id]];
        //$s->limit(1);
        $row = $table->fetchRow($where);
        return null !== $row;
    }
}
