<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements RepositoryInterface
{
    //model
    protected $model;

    protected $_auto_logical_delete = true;

    protected $_deleted_col    = 'delete_flg';
    protected $_created_at_col = 'create_date';
    protected $_updated_at_col = 'update_date';
    protected $_name = null;
    /**
     * Create a new BaseRepository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     * get model to set
     */
    abstract public function getModel();

    /**
     * Set model
     */
    public function setModel()
    {
        $this->model = app()->make(
            $this->getModel()
        );
    }

    public function model() {
        return $this->model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function find($id)
    {

        $result = $this->model->find($id);

        return $result;
    }

    public function create($attributes = [])
    {   
        if ($this->_updated_at_col && !isset($attributes[$this->_updated_at_col])) {
            $attributes[$this->_updated_at_col] = date('Y-m-d H:i:s');
        }
        if ($this->_created_at_col && !isset($attributes[$this->_created_at_col])) {
            $attributes[$this->_created_at_col] = date('Y-m-d H:i:s');
        }
        return $this->model->create($attributes);
    }

    public function update($id, $attributes = [], $columns = [])
    {   
        $attributes['update_date'] = date('Y-m-d H:i:s');
        if(is_array($id))
        {
            $where = $id;
            $results = $this->fetchAll($where, [], null, null, $columns);
            foreach($results as $result) {
                $result->update($attributes);
            }
        }
        else{
            $result = $this->find($id);
            if ($result) {
                $result->update($attributes);
                return $result;
            }
        }
        return false;
    }
    public function delete($id, $forceDelete = false, $columns = [])
    {   
        if(is_array($id))
        {
            $where = $id;
            $results = $this->fetchAll($where, [], null, null, $columns);
            foreach($results as $result) {
                if ($forceDelete) {
                    $result->forceDelete();
                }else {
                    $result->delete();
                }
            }
            return true;
        }else{
            if ($forceDelete) {
                $this->find($id)->forceDelete();
            }else {
                $this->find($id)->delete();
            }
            return true;
        }
    }

    public function fetchRow($where = [], $orders = [], $count = null, $offset = null, $columns = []) {
        if ($where instanceof \Illuminate\Database\Eloquent\Builder) {
            $select = $where;
        } else {
            $select = $this->getSelect($where , $orders, $count, $offset, $columns);
        }
        return $select->first();
    }

    public function fetchAll($where = [], $orders = [], $count = null, $offset = null, $columns = []) {
        if ($where instanceof \Illuminate\Database\Eloquent\Builder) {
            $select = $where;
        } else {
            $select = $this->getSelect($where , $orders, $count, $offset, $columns);
        }
        return $select->get();
    }

    public function getSelect($where = [], $orders = [], $count = null, $offset = null, $columns = []){
        $select = $this->model;
        if (!$this->_auto_logical_delete) {
            $select->withoutGlobalScopes();
        }
        if(!empty($where)){
            foreach($where as $condition=>$value) {
                if (is_numeric($condition) || empty($condition)) {
                    $select = $select->where([$value]);
                } else {
                    if(is_array($value)) {
                    	$select = $select->{$condition}($value[0], $value[1]);
                	} else {
                    	$select = $select->{$condition}($value);
                	}
   				}
            }
        }
        if(!empty($orders)){
            foreach($orders as $type=>$order) {
                if($order == 'RAND()') {
                    $select->inRandomOrder();
                } else if (is_numeric($type)) {
                    $select->orderBy($order);
                } else {
                    if ($order == '-sort') {
                        $select->orderByRaw($order . ' ' . $type);
                    } else {
                        $select->orderBy($order, $type);
                    }
                }
            }
        }

        if ($offset !== null) {
            $select->skip($offset);
        }
        if ($count !== null) {
            $select->take($count);
        }

        if (!empty($columns)) {
            $select->select(implode(',', $columns));
        }
        return $select;
    }

    public function getFoundRow($select) {
        return $select->count();
    }

    public function countRows($where) {
        return count($this->fetchAll($where));
    }

    public function distinctRows($where, $cols = null) {
        $select = $this->getSelect($where);
        $select->distinct();

        if(!is_null($cols)) {
            $select->selectRaw(implode(',', $cols));
        }
        // if ($where) {
        //     $this->_where($select, $where);
        // }
        // if ($this->_auto_logical_delete) {
        //     $select->where($this->_deleted_col . ' = 0');
        // }

        return $select->get();
    }

    public function getCount( $companyId, $satrtDate = null, $endDate = null, $pageTypeCode = null )
	{
		$select = $this->model->selectRaw('count(*) as `count`' ) ;
		$select->where( 'company_id', $companyId	) ;
		$select->where( 'recieve_date','>=', $satrtDate	) ;
		$select->where( 'recieve_date',	'<=', $endDate		) ;
		if( !is_null( $pageTypeCode ) )
		{
			$select->where( 'page_type_code', $pageTypeCode ) ;
		}
		$row = $select->first();
        
		return $row[ 'count' ] ;
    }
    
    public function getTableColumns() {
        $table = $this->model->getTable();
        return \DB::getSchemaBuilder()->getColumnListing($table);
    }
    public function copyRow($cols, $data = array(), $where = null, $order = null) {
        return $this->copyAll($cols, $data, $where, $order, 1);
    }

    public function copyAll($cols, $data = array(), $where = null, $order = null, $count = null, $offset = null) {
        if (!is_array($data)) {
            $data = array();
        }
        
        // if ($this->_updated_at_col && !in_array($this->_updated_at_col, $cols) && !isset($data[$this->_updated_at_col])) {
        //     $data[$this->_updated_at_col] = date('Y-m-d H:i:s');
        // }
        // if ($this->_created_at_col && !in_array($this->_created_at_col, $cols) && !isset($data[$this->_created_at_col])) {
        //     $data[$this->_created_at_col] = date('Y-m-d H:i:s');
        // }
        $select = $this->getSelect($where, $order, $count, $offset) ;
        $insertCols = array_merge($cols, array_keys($data));
        $select = $select->selectRaw(implode(',', $cols));
        foreach ($data as $value) {
            if (!($value instanceof Expr)) {
                $select = $select->selectRaw($value);
            }
            $cols[] = $value;
        };
        return $this->insertSelect($select, $insertCols);
    }
    public function insertSelect($select, $cols = []) {

        $colstr = '';
        if (count($cols)) {
            foreach ($cols as $key => $col) {
                $cols[$key] = $col ;
            }
            $colstr = ' (' . implode(',', $cols) . ')';
        }
        $nameTable = $this->model->getTable();
        $query ='INSERT INTO '. $nameTable . $colstr . ' ' . $this->toSelectRaw($select);

        DB::insert($query);
        return DB::getPdo()->lastInsertId();
    }

    public function toSelectRaw($select) {
        return vsprintf(str_replace(['?'], ['%s'], $select->toSql()), $select->getBindings());
    }

    public function copyPolling($str = "Polling:") {
        echo str_pad($str, ini_get('output_buffering'), ' ', STR_PAD_RIGHT) . date('Y-m-d H:i:s') ."<br/>\n";
        flush();
        ob_flush();
    }

    public function setAutoLogicalDelete($isAuto) {
        $this->_auto_logical_delete = $isAuto;
    }

}
