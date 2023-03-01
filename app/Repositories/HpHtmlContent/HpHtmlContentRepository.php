<?php
namespace App\Repositories\HpHtmlContent;

use App\Repositories\BaseRepository;

class HpHtmlContentRepository extends BaseRepository implements HpHtmlContentRepositoryInterface
{ 
    public function getModel()
    {
        return \App\Models\HpHtmlContent::class;
    }

    public function save($hpId, $content) {

        $s = $this->model->select();
        $s->where('hp_id', $hpId);

        // 上書き
        if ($row = $s->first()) {

            $row->content = $content;
            $row->save();

            return $row;
        }

        // 新規
        $data = array(
            'hp_id'   => $hpId,
            'content' => $content
        );
        $newRow = $this->create($data);
        return $newRow;
    }

}