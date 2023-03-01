<?php
namespace library\Custom\Assessment\Features;
use App\Repositories\HpSideParts\HpSidePartsRepository;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use Illuminate\Support\Facades\App;
class QrCode extends AbstractFeatures
{

    /**
     * QRコードをいづれかのページに設定しているか
     *
     * @return boolean
     */
    public function isUtilized()
    {
        $table = App::make(HpSidePartsRepositoryInterface::class);
        $s = $table->model()->select();
        $s->where('hp_id', $this->hp->id);
        $s->where('parts_type_code', HpSidePartsRepository::PARTS_QR);
        $s->take(1);
        $row = $s->first();

        return $row !== null;
    }
}
