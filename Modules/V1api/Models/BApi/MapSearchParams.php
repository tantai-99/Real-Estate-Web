<?php
namespace Modules\V1api\Models\BApi;

class MapSearchParams extends BukkenSearchParams
{
    public function setNoCoordinates($no_coordinates) {
        $this->no_coordinates = $no_coordinates;
    }
    public function setNoBukkens($no_bukkens) {
        $this->no_bukkens = $no_bukkens;
    }
    public function setmaxCoordinates($max_coordinates) {
        $this->max_coordinates = $max_coordinates;
    }
    public function setIdoKeidoNansei($ido_keido_nansei) {
        $this->ido_keido_nansei = $ido_keido_nansei;
    }
    public function setIdoKeidoHokuto($ido_keido_hokuto) {
        $this->ido_keido_hokuto = $ido_keido_hokuto;
    }
    public function disableFacets() {
        $this->facets = null;
    }
    public function setCenterCondition($params){
        $this->setPerPage(1);
        $this->setNoCoordinates(1);
        $this->disableFacets();
        $this->setOrderBy($params->getSort());
        $this->data_model =['ido', 'keido'];
    }
    public function setCoordinatesGroupingCd($val){
        $this->coordinates_grouping_cd = $val;
    }
}