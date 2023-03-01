<?php
namespace App\Repositories\HpAssessment;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
class HpAssessmentRepository extends BaseRepository implements HpAssessmentRepositoryInterface
{

    protected $_name = 'hp_assessment';

    /**
     * @param int $hp_id
     * @param string $min_date
     * @param string $max_date
     * @return array
     */

    public function getModel()
    {
        return \App\Models\HpAssessment::class;
    }

    public function fetchMonthlyAverageInRange($hp_id, $min_date, $max_date)
    {
        $s = $this->model->select('hp_id');
        $s->selectRaw('AVG(point) as point');
        $s->selectRaw('DATE_FORMAT(date,"%Y-%m") as month');
        $s->where('hp_id', $hp_id);
        $s->where('date', '>=', $min_date);
        $s->where('date','<=', $max_date);
        $s->groupByRaw('YEAR(date),MONTH(date),hp_id');
        return $s->get();
    }
}