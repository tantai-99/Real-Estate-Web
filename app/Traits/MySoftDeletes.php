<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait MySoftDeletes
{
    use SoftDeletes;

    public static function bootSoftDeletes()
    {
        
    }

    protected function runSoftDelete()
    {
        $query = $this->newQueryWithoutScopes()->where($this->getKeyName(), $this->getKey());

        $columns = [$this->getDeletedAtColumn() => 1];

        $this->{$this->getDeletedAtColumn()} = 1;

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));

        $this->fireModelEvent('trashed', false);
    }

}