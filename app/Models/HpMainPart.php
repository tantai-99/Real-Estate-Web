<?php
namespace App\Models;

use Library\Custom\Model\Lists\Original;
use App\Traits\MySoftDeletes;
use App\Repositories\HpMainParts\HpMainPartsRepository;
 
class HpMainPart extends Model {
    use MySoftDeletes;

    protected $table = 'hp_main_parts';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'update_date';


    protected $fillable = [
        "id",
        "parts_type_code",
        "sort",
        "column_sort",
        "area_id",
        "page_id",
        "hp_id",
        "copied_id",
        "display_flg",
        "attr_1",
        "attr_2",
        "attr_3",
        "attr_4",
        "attr_5",
        "attr_6",
        "attr_7",
        "attr_8",
        "attr_9",
        "attr_10",
        "attr_11",
        "attr_12",
        "delete_flg",
        "create_id",
        "create_date",
        "update_id",
        "update_date",
    ];

    public function getAllNotificationSettings($pageId){
        $partCode = HpMainPartsRepository::PARTS_INFO_LIST;
        $select = $this->where('page_id', $pageId);
        // $select->where('page_id', $pageId);
        $select->where('parts_type_code', $partCode);
        $select->orderBy(Original::$EXTEND_INFO_LIST['notification_type']);
        return $select->get();
        // return $table->fetchAll($select);
    }
}