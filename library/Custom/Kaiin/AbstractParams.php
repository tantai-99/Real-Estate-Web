<?php
namespace Library\Custom\Kaiin;


abstract class AbstractParams
{

    /**
     * オブジェクトに定義されているprotected変数を
     * 変数名をkeyに、変数値をvalueにした、
     * API用のパラメータを生成して返します。
     * 変数値が配列の場合、","で連結した文字列に変換します。
     *
     * @param $instance Paramsのインスタンス
     * @return API用のQuery
     */
    public function buildQuery ($instance)
    {
        if (is_null($instance)) return null;
         
        $query = '?';
        $ref = new \ReflectionClass($instance);
        $props = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);
        $isFirst = true;
        foreach ($props as $prop)
        {
            $prop->setAccessible(true);
            $val = $prop->getValue($instance);
//            if (! $val)
            if ($val == "")
            {
                continue;
            }
            if (is_array($val))
            {
                $val = implode(",", $val);
            }
            
            if ($isFirst)
            {
                $isFirst = false;
            }
            else
            {
                $query = $query . '&';
            }
            $query = $query . $prop->getName() . '=' . $val;
        }
        return $query;
    }
}