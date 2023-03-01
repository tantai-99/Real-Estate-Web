<?php
namespace Library\Custom\View\Helper;
/**
 * PV/指数のチャートを表示
 */
class PvPointChart extends  HelperAbstract
{
    /**
     * @param array $pv
     * @param array $points
     * @param int $max_point
     * @return string
     */
    public function pvPointChart(array $pv, array $points, $max_point)
    {
        $html = '<div class="pv-point-chart"'
            . "data-plot-pv='" . json_encode($this->formatArray($pv)) . "'"
            . "data-plot-point='" . json_encode($this->formatArray($points)) . "'"
            . "data-plot-max-point='{$max_point}'"
            . "></div>";

        return $html;
    }

    private function formatArray(array $data_set)
    {
        $results = [];
        foreach ($data_set as $key_date => $value) {
            $date_for_browser = date('m/d/Y', strtotime($key_date));
            $results[] = [$date_for_browser, $value];
        }

        return $results;
    }
}