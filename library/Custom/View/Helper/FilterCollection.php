<?php
namespace Library\Custom\View\Helper;

/**
 * Viewに渡されたコレクション(配列)をフィルタリング、ソート、スライスする
 */
class FilterCollection extends  HelperAbstract
{
    public function filterCollection($collection, array $filter, array $sort = null, $count = null)
    {
        $matched = array();
        foreach ($collection as $item) {
            if ($item[$filter[0]] == $filter[1] && $item[$filter[2]] == $filter[3]) {
                $matched[] = $item;
            }
        }

        $sort_key = $sort[0];
        if (!is_array($sort_key)) {
            $sort_key = array($sort_key);
        }
        $sort_direction = $sort[1];
        if (!is_array($sort_direction)) {
            $sort_direction = array($sort_direction);
        }

        usort($matched, function ($a, $b) use ($sort_key, $sort_direction) {
            $flag = 0;
            for ($i = 0; $flag === 0 && isset($sort_key[$i]); $i++) {
                $flag = strcasecmp($a[$sort_key[$i]], $b[$sort_key[$i]]);
                if (0 !== $flag && $sort_direction[$i] === 'DESC') {
                    $flag *= -1;
                }
            }

            return $flag;
        });

        if ($count > 0) {
            return array_slice($matched, 0, $count);
        } else {
            return $matched;
        }
    }

    public function filterCollectionTop($collection, array $filter, array $sort = null, $count = null)
    {
        $matched = array();
        foreach ($collection as $item) {
            if ($item[$filter[0]] == $filter[1] && $item[$filter[2]] == $filter[3] && $item[$filter[4]] == $filter[5]) {
                $matched[] = $item;
            }
        }
        
        $sort_key = $sort[0];
        if (!is_array($sort_key)) {
            $sort_key = array($sort_key);
        }
        $sort_direction = $sort[1];
        if (!is_array($sort_direction)) {
            $sort_direction = array($sort_direction);
        }

        usort($matched, function ($a, $b) use ($sort_key, $sort_direction) {
            $flag = 0;
            for ($i = 0; $flag === 0 && isset($sort_key[$i]); $i++) {
                $flag = strcasecmp($a[$sort_key[$i]], $b[$sort_key[$i]]);
                if (0 !== $flag && $sort_direction[$i] === 'DESC') {
                    $flag *= -1;
                }
            }

            return $flag;
        });

        if ($count > 0) {
            return array_slice($matched, 0, $count);
        } else {
            return $matched;
        }
    }

}
