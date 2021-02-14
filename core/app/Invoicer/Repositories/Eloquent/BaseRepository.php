<?php namespace App\Invoicer\Repositories\Eloquent;

use Illuminate\Container\Container as App;

abstract class BaseRepository{
    private $app;
	/**
	 * The repository model
	 *
	 * @var \Illuminate\Database\Eloquent\Model
	 */
	protected $model;
	/**
	 * The query builder
	 *
	 * @var \Illuminate\Database\Eloquent\Builder
	 */
	protected $query;
	/**
	 * Alias for the query limit
	 *
	 * @var int
	 */
	protected $take;
	/**
	 * Array of related models to eager load
	 *
	 * @var array
	 */
	protected $with = array();
	/**
	 * Array of one or more where clause parameters
	 *
	 * @var array
	 */
	protected $wheres = array();
	/**
	 * Array of one or more where in clause parameters
	 *
	 * @var array
	 */
	protected $whereIns = array();
    public function __construct(App $app) {
        $this->app = $app;
        $this->makeModel();
    }
    public abstract function model();
	/**
	 * Get all the model records in the database
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function all(){
		$this->newQuery()->eagerLoad();
		$models = $this->query->get();
		$this->unsetClauses();
		return $models;
	}
	/**
	 * Count the number of specified model records in the database
	 *
	 * @return int
	 */
	public function count(){
		return $this->get()->count();
	}
	/**
	 * Create a new model record in the database
	 *
	 * @param array $data
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function create(array $data){
		$this->unsetClauses();
		return $this->model->create($data);
	}
	/**
	 * Delete one or more model records from the database
	 *
	 * @return mixed
	 */
	public function delete(){
		$this->newQuery()->setClauses();
		$result = $this->query->delete();
		$this->unsetClauses();
		return $result;
	}
	/**
	 * Delete the specified model record from the database
	 *
	 * @param $id
	 *
	 * @return bool|null
	 * @throws \Exception
	 */
	public function deleteById($id){
		$this->unsetClauses();
		return $this->getById($id)->delete();
	}
	public function deleteMultipleById(array $ids){
		return $this->model->destroy($ids);
	}
	/**
	 * Get the first specified model record from the database
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function first(){
		$this->newQuery()->eagerLoad()->setClauses();
		$model = $this->query->first();
		$this->unsetClauses();
		return $model;
	}
	/**
	 * Get all the specified model records in the database
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function get(){
		$this->newQuery()->eagerLoad()->setClauses();
		$models = $this->query->get();
		$this->unsetClauses();
		return $models;
	}
	/**
	 * Get the specified model record from the database
	 *
	 * @param $id
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getById($id){
		$this->unsetClauses();
		$this->newQuery()->eagerLoad();
		return $this->query->find($id);
	}
    public function makeModel() {
        $model = $this->app->make($this->model());
        return $this->model = $model;
    }
	/**
	 * Set the query limit
	 *
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function limit($limit){
		$this->take = $limit;
		return $this;
	}
	/**
	 * Update the specified model record in the database
	 *
	 * @param       $id
	 * @param array $data
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function updateById($id, array $data){
		$this->unsetClauses();
		$model = $this->getById($id);
		$model->update($data);
		return $model;
	}
	/**
	 * Add a simple where clause to the query
	 *
	 * @param string $column
	 * @param string $value
	 * @param string $operator
	 *
	 * @return $this
	 */
	public function where($column, $value, $operator = '='){
		$this->wheres[] = compact('column', 'value', 'operator');
		return $this;
	}
	/**
	 * Add a simple where in clause to the query
	 *
	 * @param string $column
	 * @param mixed  $values
	 *
	 * @return $this
	 */
	public function whereIn($column, $values){
		$values = is_array($values) ? $values : array($values);
		$this->whereIns[] = compact('column', 'values');
		return $this;
	}
	/**
	 * Set Eloquent relationships to eager load
	 *
	 * @param $relations
	 *
	 * @return $this
	 */
	public function with($relations){
		if (is_string($relations)) {
		    $relations = func_get_args();
		}
		$this->with = $relations;
		return $this;
	}
	/**
	 * Create a new instance of the model's query builder
	 *
	 * @return $this
	 */
	protected function newQuery(){
		$this->query = $this->model->newQuery();
		return $this;
	}
	/**
	 * Add relationships to the query builder to eager load
	 *
	 * @return $this
	 */
	protected function eagerLoad(){
		foreach($this->with as $relation) {
			$this->query->with($relation);
		}
		return $this;
	}
	/**
	 * Set clauses on the query builder
	 *
	 * @return $this
	 */
	protected function setClauses(){
		foreach($this->wheres as $where) {
			$this->query->where($where['column'], $where['operator'], $where['value']);
		}
		foreach($this->whereIns as $whereIn) {
			$this->query->whereIn($whereIn['column'], $whereIn['values']);
		}
		if(isset($this->take) and ! is_null($this->take)) {
			$this->query->take($this->take);
		}
		return $this;
	}
	/**
	 * Reset the query clause parameter arrays
	 *
	 * @return $this
	 */
	protected function unsetClauses(){
		$this->wheres = array();
		$this->whereIns = array();
		$this->take = null;
		return $this;
	}
}