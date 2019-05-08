<?php
/**
 * Created by PhpStorm.
 * User: tan.nguyen
 * Date: 2/22/2019
 * Time: 2:59 PM
 */

namespace App\Repositories;

use App\Repositories\RepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use App\Helper\JONWebToken as JWT;

abstract class BaseRepository implements RepositoryInterface
{

    protected $app;

    protected $model;

    protected $fieldSearchable = array();

    protected $validator;

    protected $response;

    protected $jwt;

    public function __construct(Application $app, ResponseService $response, JWT $jwt)
    {
        $this->app = $app;
        $this->response = $response;
        $this->jwt = $jwt;
        $this->makeModel();
    }

    public function resetModel()
    {
        $this->makeModel();
    }

    abstract protected function model();

    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    public function all($columns = array('*'))
    {

        if ($this->model instanceof \Illuminate\Database\Eloquent\Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }
        $this->resetModel();
        return $results;
    }

    public function paginate($params = array(), $limit = PAGINATE, $columns = array('*'))
    {
        $fields = self::getTableColumns();
        $order = array('id', 'desc');
        if (isset($params['order_by'])) {
            $order = explode('.', $params['order_by']);
        }
        foreach ($params as $key => $value) {
            if (!isset($params[$key]) || !in_array($key, $fields)) {
                unset($params[$key]);
            }
        }
        $results = $this->model->where($params)->orderBy($order[0], $order[1])->paginate($limit, $columns);
        return $results;
    }

    /**
     * @param array $params
     * @return Eloquent
     * @author tannd
     */
    public function whereArray($params = array())
    {
        $fields = self::getTableColumns();
        foreach ($params as $key => $value) {
            if (!isset($params[$key]) || !in_array($key, $fields) || $value === '') {
                unset($params[$key]);
            }
        }
        $results = $this->model->where($params);
        return $results;
    }

    public function find($id, $columns = array('*'))
    {
        $model = $this->model->find($id, $columns);
        $this->resetModel();
        return $model;
    }

    public function findByField($field, $value = null, $columns = array('*'))
    {
        $model = $this->model->where($field, $value)->get($columns);
        $this->resetModel();
        return $model;
    }

    public function findWhere(array $where, $input = array('*'), $columns = array('*'))
    {
        $model = $this->model->where($input)->get($columns);
        $this->resetModel();

        return $model;
    }

    public function findWhereIn($field, array $values, $columns = array('*'))
    {
        $model = $this->model->whereIn($field, $values)->get($columns);
        $this->resetModel();
        return $model;
    }

    public function findWhereNotIn($field, array $values, $columns = array('*'))
    {
        $model = $this->model->whereNotIn($field, $values)->get($columns);
        $this->resetModel();
        return $model;
    }

    public function create(array $attributes)
    {
        if (!is_null($this->validator)) {
            $this->validator->with($attributes)
                ->passesOrFail(ValidatorInterface::RULE_CREATE);
        }
        return $this->model->create($attributes);
    }

    public function update(array $attributes, $id)
    {

        if (!is_null($this->validator)) {
            $this->validator->with($attributes)
                ->setId($id)
                ->passesOrFail(ValidatorInterface::RULE_UPDATE);
        }

        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public function delete($id)
    {
        $model = $this->find($id);
        $deleted = $model->delete();
        return $deleted;
    }

    public function with($relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    public function getTableColumns()
    {
        return \Schema::getColumnListing($this->model->getTable());
    }

}
