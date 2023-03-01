<?php
namespace Modules\V1api\Models;

use Library\Custom\Estate\Group;
use Illuminate\Support\Facades\App;
use App\Repositories\ViewBukken\ViewBukkenRepositoryInterface;
use App\Repositories\FavoriteBukken\FavoriteBukkenRepositoryInterface;
/**
 * 行動情報関連テーブル操作 
 *
 */
class OperationHistory
{
    private $comId;
    public function __construct($comId)
    {
        $this->comId = $comId;
    }

    /**
     * 最近見た物件追加
    */
    public function updateHistory($userId, $bukkenIds) {
        $viewBukken = App::make(ViewBukkenRepositoryInterface::class);
        $viewBukken->updateOperationHistory($userId, $this->getBukkenNos($bukkenIds));
    }

    /**
     * お気に入り物件追加
    */
    public function updateFavorite($userId, $bukkenIds) {
        $favoriteBukken = App::make(FavoriteBukkenRepositoryInterface::class);
        $favoriteBukken->updateOperationHistory($userId, $this->getBukkenNos($bukkenIds));
    }

    /**
     * お気に入り物件削除
    */
    public function deleteFavorite($userId, $bukkenIds) {
        $favoriteBukken = App::make(FavoriteBukkenRepositoryInterface::class);
        $favoriteBukken->deleteFavorite($userId, $this->getBukkenNos($bukkenIds));
    }

    /**
     * 引数の物件IDリストに該当する物件番号リストを返す
    */
    private function getBukkenNos($bukkenIds) {
        // サイト主契約会員グループの会員リンクNoのリスト
        $estateGroup = new Group();
        $groupKaiLinkNoList = $estateGroup->getGroupKaiLinkNoList($this->comId);
        // BApi用パラメータを作成して、物件APIから該当の物件情報を取得
        $bukkenApiObj    = new BApi\BukkenSearch();
        $bukkenApiParams = new BApi\BukkenSearchParams();
        $bukkenApiParams->setGroupId($this->comId);
        $bukkenApiParams->setKaiinLinkNo($groupKaiLinkNoList);
        $bukkenApiParams->setId($bukkenIds);
        $bukkenApiParams->setPerPage('50');
        $bukken = $bukkenApiObj->search($bukkenApiParams);
        return array_map(function($n){return $n['display_model']['bukken_no'];}, $bukken['bukkens']);
    }
}
