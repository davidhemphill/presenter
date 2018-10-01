<?php

namespace Hemp\Presenter;

use ArrayAccess;
use BadMethodCallException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;

abstract class Presenter implements Jsonable, Arrayable, ArrayAccess
{
    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [];

    /**
     * The attributes that should be hidden in arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The cache of the mutated attributes for each class.
     *
     * @var array
     */
    protected static $mutatorCache;

    /**
     * Indicates whether attributes are snake cased on arrays.
     *
     * @var bool
     */
    public static $snakeAttributes = true;

    /**
     * The decorated model.
     *
     * @var Illuminate/Database/Eloquent/Model|Hemp/Presenter/Presenter
     */
    protected $model;

    /**
     * The original, undecorated model.
     *
     * @var Illuminate/Database/Eloquent/Model
     */
    protected $originalModel;

    /**
     * Create a new instance of the Presenter.
     *
     * @param Illuminate/Database/Eloquent/Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;

        if ($this->model instanceof self) {
            $model = $model->getOriginalModel();
        }

        $this->originalModel = $model;
    }

    /**
     * Get the decorated model.
     *
     * @return Illuminate/Database/Eloquent/Model|Hemp/Presneter/Presenter
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the original, undecorated model.
     *
     * @return Illuminate/Database/Eloquent/Model
     */
    public function getOriginalModel()
    {
        return $this->originalModel;
    }

    /**
     * Pass magic properties to accessors.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get'.studly_case($name).'Attribute';

        if (method_exists($this, $method)) {
            return $this->{$method}($name);
        }

        return $this->model->{$name};
    }

    /**
     * Call the model's version of the method if available.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
    }

    /**
     * Get the visible attributes for the model.
     *
     * @return array
     */
    public function getVisiblePresenterAttributes()
    {
        return $this->visible;
    }

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHiddenPresenterAttributes()
    {
        return $this->hidden;
    }

    /**
     * Convert the decorated instance to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert the decorated instance to JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the decorator instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $mutatedAttributes = $this->mutatorsToArray();

        $all = array_merge($this->model->toArray(), $mutatedAttributes);
        if (!static::$snakeAttributes) {
            $all = array_combine(
                array_map(function ($k) {
                    return Str::camel($k);
                }, array_keys($all)),
                $all
            );
        }

        $items = $this->getArrayableItems($all);

        if (!static::$snakeAttributes) {
            $items = array_combine(
                array_map(function ($k) {
                    return Str::camel($k);
                }, array_keys($items)),
                $items
            );
        }

        return array_intersect_key($all, $items);
    }

    /**
     * Convert the decorators instance's mutators to an array.
     *
     * @return array
     */
    public function mutatorsToArray()
    {
        $mutatedAttributes = [];

        $mutators = $this->getMutatedAttributes();

        foreach ($mutators as $mutator) {
            $mutatedAttributes[Str::snake($mutator)] = $this->mutateAttribute($mutator);
        }

        return $mutatedAttributes;
    }

    /**
     * Get the mutated attributes for a given instance.
     *
     * @return array
     */
    public function getMutatedAttributes()
    {
        $class = static::class;

        if (!isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }

        return static::$mutatorCache[$class];
    }

    /**
     * Extract and cache all the mutated attributes of a class.
     *
     * @param string $class
     *
     * @return void
     */
    public static function cacheMutatedAttributes($class)
    {
        $mutatedAttributes = [];

        // Here we will extract all of the mutated attributes so that we can quickly
        // spin through them after we export models to their array form, which we
        // need to be fast. This'll let us know the attributes that can mutate.
        if (preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches)) {
            foreach ($matches[1] as $match) {
                if (static::$snakeAttributes) {
                    $match = Str::snake($match);
                }

                $mutatedAttributes[] = lcfirst($match);
            }
        }

        static::$mutatorCache[$class] = $mutatedAttributes;
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function mutateAttribute($key)
    {
        return $this->{'get'.Str::studly($key).'Attribute'}();
    }

    /**
     * Get an attribute array of all arrayable values.
     *
     * @param array $values
     *
     * @return array
     */
    protected function getArrayableItems($values)
    {
        if (count($this->getVisiblePresenterAttributes()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisiblePresenterAttributes()));
        }

        if (count($this->getHiddenPresenterAttributes()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHiddenPresenterAttributes()));
        }

        return $values;
    }

    /**
     * Return true if the property is set and not null.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * Return true if the offset exists and is not null.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->{$offset} !== null;
    }

    /**
     * Return the value at the specified offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Required implementation to satisfy the ArrayAccess interface,
     * but throws as a BadMethodCallException as this is a read only
     * implementation.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Not implemented - read only implementation.');
    }

    /**
     * Required implementation to satisfy the ArrayAccess interface,
     * but throws as a BadMethodCallException as this is a read only
     * implementation.
     *
     * @param mixed $offset
     *
     * @throws \BadMethodCallException
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Not implemented - read only implementation.');
    }
}
