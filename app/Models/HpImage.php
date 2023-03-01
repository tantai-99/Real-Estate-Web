<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
use App\Models\HpMainPart;
use Illuminate\Support\Facades\App;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;

class HpImage extends Model
{
    use MySoftDeletes;

    protected $table = 'hp_image';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    protected $primaryKey = 'aid';

    protected $fillable = [
    	'aid', 
    	'id', 
    	'type', 
    	'sys_name', 
    	'title', 
    	'hp_image_content_id', 
    	'category_id', 
    	'hp_id', 
    	'delete_flg', 
    	'create_id', 
    	'create_date', 
    	'update_id', 
    	'update_date',
    ];
    public function hpImageContent($col)
    {   
        return $this->hasMany(HpImageContent::class, $col);
    }
    public function hpImageUser()
    {   
        return $this->hasOne(HpImageUser::class, 'hp_image_id', 'id');
    }
    public function getContent() {
		$table = App::make(HpImageContentRepositoryInterface::class);
		return $table->fetchRow([['id', $this->hp_image_content_id], ['hp_id', $this->hp_id]]);
	}

	public function toResponseArray() {
		return array(
			'id'	=> $this->id,
			'title'	=> $this->title,
			'hp_image_content_id' => $this->hp_image_content_id,
			'category_id' => $this->category_id,
			'hp_id' => $this->hp_id,
			'url' => '/image/hp-image?id=' . $this->hp_image_content_id,
		);
	}
    
}