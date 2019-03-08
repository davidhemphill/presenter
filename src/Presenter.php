<?php

namespace Hemp\Presenter;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

abstract class Presenter
{
    /**
     * Whether to snake case the attributes.
     *
     * @var bool
     */
    public $snakeCaseAttributes = true;

    /**
     * The Model being presented.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * Create a new Presenter instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Call the Model's version of the method if available.
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
     * Return the value of a magic accessor.
     *
     * @param string $name
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
        $modelArray = $this->model->toArray();

        return $this->processKeys(array_merge(
            $modelArray,
            $this->mutatedAttributes()
        ));
    }

    /**
     * Return the mutated attributes for the Presenter.
     *
     * @return array
     */
    protected function mutatedAttributes()
    {
        return collect($this->mutatableAttributes())->mapWithKeys(function ($attribute) {
            $attributeKey = $this->snakeCaseAttributes ? lcfirst(Str::snake($attribute)) : lcfirst(Str::camel($attribute));

            return [$attributeKey => $this->mutateAttribute($attribute)];
        })->all();
    }

    /**
     * Return the mutable attributes for the Presenter;.
     *
     * @return array
     */
    protected function mutatableAttributes()
    {
        $mutatable = [];

        $classMethods = get_class_methods(static::class);

        if (preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', $classMethods), $matches)) {
            foreach ($matches[1] as $match) {
                $mutatable[] = lcfirst(Str::snake($match));
            }
        }

        return $mutatable;
    }

    /**
     * Mutate the given Presenter attribute.
     *
     * @param string $attribute
     * @return string
     */
    protected function mutateAttribute($attribute)
    {
        return $this->{$this->getStudlyAttributeMethod($attribute)}($this->model);
    }

    /**
     * Get the studly attribute method name.
     *
     * @param string $attribute
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
     * @param array $attributes
     * @return array
     */
    protected function processKeys($attributes)
    {
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            return [
                lcfirst($this->snakeCaseAttributes ? Str::snake($key) : Str::camel($key)) => $value,
            ];
        })->all();
    }
}
