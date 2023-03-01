<?php
 
namespace App\Casts;
 
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
 
class AsSubString implements CastsAttributes
{

    /**
     * Create a new cast class instance.
     *
     * @param  string|null  $limit
     * @return void
     */
    public function __construct($limit = null)
    {
        $this->limit = $limit;
    }
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        return $value;
    }
 
    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if(strlen($value) < $this->limit) {
            return $value;
        }
        return substr($value, 0, $this->limit);
    }
}