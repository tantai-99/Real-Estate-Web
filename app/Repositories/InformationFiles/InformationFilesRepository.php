<?php

namespace App\Repositories\InformationFiles;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use function Symfony\Component\Translation\t;

class InformationFilesRepository extends BaseRepository implements InformationFilesRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\InformationFiles::class;
    }

    protected $_name = 'information_files';

		/**
		 * �擾����
		 */
        public function getDataForId($id) {
			$select = $this->model->select();
			$select->where("id", $id);
			return $select->first();
        }

		/**
		 * �擾����
		 */
        public function getDataForInformationId($information_id) {

			$select = $this->model->select();
			$select->where("information_id", $information_id);
			return $select->get();
        }

}
