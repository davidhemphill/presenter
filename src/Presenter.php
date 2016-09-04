<?php

namespace Hemp;

class Presenter
{
    /**
     * The decorated model
     * @var Illuminate/Database/Eloquent/Model
     */
    protected $model;

    /**
     * Create a new instance of the Presenter
     * @param Illuminate/Database/Eloquent/Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Get the decorated model
     * @return Illuminate/Database/Eloquent/Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Pass magic properties to accessors
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        try {
            $method = 'get' . studly_case($name) . 'Attribute';

            if (method_exists($this, $method)) {
                return $this->{$method}($name);
            }

            if (method_exists($this->model, $name)) {
                $this->model->{$name}(func_get_args());
            }

            return $this->model->{$name};
        } catch (Exception $e) {
            throw new Exception("Property [{$name}] could not be resolved.");
        }
    }

    /*
     * Call the model's version of the method if available
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
    }
}
