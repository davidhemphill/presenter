<?php

namespace Hemp\Presenter;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mockery\Exception\BadMethodCallException;

abstract class Presenter implements ArrayAccess, Arrayable, Jsonable
{
    /**
     * Whether to snake case the attributes.
     *
     * @var bool
     */
    public $snakeCase = true;

    /**
     * The Model being presented.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * The hidden attributes on the Presenter.
     *
     * @var array
     */
    public $hidden = [];

    /**
     * The visible attributes on the Presenter.
     *
     * @var array
     */
    public $visible = [];

    /**
     * Create a new Presenter instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|mixed  $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Render the output using camelCase keys.
     *
     * @return $this
     */
    public function camelCase()
    {
        $this->snakeCase = false;

        return $this;
    }

    /**
     * Render the output using snake_case keys.
     *
     * @return $this
     */
    public function snakeCase()
    {
        $this->snakeCase = true;

        return $this;
    }

    /**
     * Create a new Presenter instance.
     *
     * @param  Model  $model
     * @param  \Hemp\Presenter\Presenter|null  $presenter
     * @return \Hemp\Presenter\Presenter
     */
    public static function make(Model $model, $presenter = null)
    {
        return (new PresenterFactory)($model, $presenter ?? static::class);
    }

    /**
     * Return a collection of presented models.
     *
     * @param  array|\Illuminate\Support\Collection  $models
     * @return \Illuminate\Support\Collection
     */
    public static function collection($models, $presenter = null)
    {
        return collect($models)->present($presenter ?? static::class);
    }

    /**
     * Return the keys for the presented model and cache the value.
     *
     * @return array
     */
    protected function modelKeys()
    {
        return array_keys($this->model->toArray());
    }

    /**
     * Call the Model's version of the method if available.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
    }

    /**
     * Return the value of a magic accessor.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($attribute)
    {
        $method = $this->getStudlyAttributeMethod($attribute);

        // If the magic getter exists on this Presenter, let's call it and return the value,
        // passing in the original model instance, so the user can mutate it first.
        if (method_exists($this, $method)) {
            return $this->{$method}($this->model);
        }

        // If not, then let's delegate the magic call to the underlying Model instance.
        return $this->model->{$attribute};
    }

    /**
     * Convert the Presenter to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert the Presenter to a JSON string.
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the Presenter to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->processKeys(
            array_merge(
                $this->removeHiddenAttributes($this->model->toArray()),
                $this->additionalAttributes()
            )
        );
    }

    /**
     * Remove the non-visible attributes from the output.
     *
     * @param  array  $array
     * @return array
     */
    protected function removeHiddenAttributes($attributes)
    {
        return Arr::only($attributes, $this->visibleAttributes());
    }

    /**
     * Determine the visible attributes for the Presenter, taking into account the key might exist
     * in both the `hidden` and `visible` arrays. If a key is found in both, then let's assume
     * it is `visible`.
     *
     * @return void
     */
    public function visibleAttributes()
    {
        if (empty($this->visible)) {
            return array_flip(Arr::except(array_flip($this->modelKeys()), $this->hidden));
        }

        return array_flip(Arr::only(array_flip($this->modelKeys()), $this->visible));
    }

    /**
     * Return the additional attributes for the Presenter.
     *
     * @return array
     */
    protected function additionalAttributes()
    {
        return collect($this->availableAttributes())->mapWithKeys(function ($attribute) {
            $attributeKey = $this->snakeCase ? lcfirst(Str::snake($attribute)) : lcfirst(Str::camel($attribute));

            return [$attributeKey => $this->mutateAttribute($attribute)];
        })->all();
    }

    /**
     * Return the mutable attributes for the Presenter;.
     *
     * @return array
     */
    protected function availableAttributes()
    {
        return collect($this->getAttributeMatches())->map(function ($attribute) {
            return lcfirst(Str::snake($attribute));
        });
    }

    /**
     * Get any attributes with accessors defined on the Presenter.
     *
     * @return array
     */
    protected function getAttributeMatches()
    {
        return with(implode(';', get_class_methods(static::class)), function ($attributeMethods) {
            preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', $attributeMethods, $matches);

            return $matches[1];
        });
    }

    /**
     * Mutate the given Presenter attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    protected function mutateAttribute($attribute)
    {
        return $this->{$this->getStudlyAttributeMethod($attribute)}($this->model);
    }

    /**
     * Get the studly attribute method name.
     *
     * @param  string  $attribute
     * @return string
     */
    protected function getStudlyAttributeMethod($attribute)
    {
        $studlyAttribute = Str::studly($attribute);

        return "get{$studlyAttribute}Attribute";
    }

    /**
     * Process the given attribute's keys.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function processKeys($attributes)
    {
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            return [
                lcfirst($this->snakeCase ? Str::snake($key) : Str::camel($key)) => $value,
            ];
        })->all();
    }

    /**
     * Determine if the given offset exists on the Presenter.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->{$offset} !== null;
    }

    /**
     * Retrieve the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  string  $value
     *
     * @throws BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Hemp/Presenter does not support write methods');
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     *
     * @throws BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Hemp/Presenter does not support write methods');
    }
}
