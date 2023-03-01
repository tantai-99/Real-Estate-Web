<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
use App\Models\HpMainPart;
use Illuminate\Support\Facades\App;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;

class HpFile2 extends Model
{
    use MySoftDeletes;

    protected $table = 'hp_file2';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    protected $primaryKey = 'aid';

    protected $fillable = [
    	'aid', 
    	'id', 
    	'type', 
    	'sys_name', 
    	'title', 
    	'hp_file2_content_id', 
    	'category_id', 
    	'hp_id', 
    	'delete_flg', 
    	'create_id', 
    	'create_date', 
    	'update_id', 
    	'update_date',
    ];
    public function hpFile2Content($col)
    {   
        return $this->hasMany(HpFile2Content::class, $col);
    }
    public function hpFile2User()
    {   
        return $this->hasOne(HpFile2User::class, 'hp_file2_id', 'id');
    }

    public function getContent() {
        $table = App::make(HpFile2ContentRepositoryInterface::class) ;
        return $table->fetchRow( [[ 'id', $this->hp_file2_content_id], ['hp_id', $this->hp_id]] ) ;
    }

    public function toResponseArray() {
        return array(
            'id'                    => $this->id                                            ,
            'title'                 => $this->title                                         ,
            'hp_file2_content_id'   => $this->hp_file2_content_id                           ,
            'category_id'           => $this->category_id                                   ,
            'hp_id'                 => $this->hp_id                                         ,
            'url'                   => "/file/hp-file2?id={$this->hp_file2_content_id}"     ,
        ) ;
    }
}