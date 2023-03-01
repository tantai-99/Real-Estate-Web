<?php
namespace App\Repositories\OriginalSetting;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Repositories\Company\CompanyRepositoryInterface;

use function Symfony\Component\Translation\t;

class OriginalSettingRepository extends BaseRepository implements OriginalSettingRepositoryInterface
{
    
    protected $_name = 'second_estate';
    protected $_rowClass = 'SecondEstate_Row';


    public function getModel()
    {
        return \App\Models\OriginalSetting::class;
    }

    public function getDataForCompanyId($company_id) {
        return $this->model->select()->where('company_id', $company_id)->first();

    }
    
    public function hasChanged($company_id, $datetime)
    {
        if ($this->timezone !== $datetime->getTimezone()->getName()) {
            $datetime->setTimezone($this->timezone);
        }
        
        return $this->countRows(array(
            ['company_id', $company_id],
            ['update_date', '>', $datetime->format('Y-m-d H:i:s')],
        ), ['id']);
    }

    /**
     * @param $select
     * @return array
     */
    public function fetchWithCompany($select){

        $companyTable = \App::make(CompanyRepositoryInterface::class);
        $settings = $this->fetchAll($select);

        if(!$settings || empty($settings) || is_null($settings) || count($settings) == 0) return [];

        $settingsID = [];

        foreach($settings as $v){
            $settingsID[] = $v->company_id;
        }

        $companies = $companyTable->fetchAll([
            'whereIn' => ['id' , $settingsID]
        ]);

        $data = [];

        foreach($settings as $v){
            $item['data'] = $v;
            $item['company'] = null;
            if($companies && !empty($companies)){
                foreach($companies as $company){
                    if($company['id'] == $v['company_id']){
                        $item['company'] = $company;
                        break;
                    }
                }
            }
            $data[] = $item;
         }

        return $data;
    }

    public function getSelectTop($target_date) {
        $select = $this->model->select();
        $select->where('start_date', '<=', $target_date);
        $select->whereRaw('(end_date IS NULL OR end_date > "' . $target_date . '")');
        $select->where('all_update_top', 0);
        return $select;
    }
}