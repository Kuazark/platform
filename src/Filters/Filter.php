<?php

declare(strict_types=1);

namespace Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Field;

abstract class Filter
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var array
     */
    public $parameters;

    /**
     * @var bool
     */
    public $display = true;

    /**
     * Current app language.
     *
     * @var string
     */
    public $lang;

    /**
     * Filter constructor.
     */
    public function __construct()
    {
        $this->request = request();
        $this->lang = app()->getLocale();
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function filter(Builder $builder): Builder
    {
        if (is_null($this->parameters) || $this->request->filled($this->parameters)) {
            return $this->run($builder);
        }

        return $builder;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    abstract public function run(Builder $builder): Builder;

    /**
     * @return Field[]
     */
    public function display() : array
    {
        //
    }

    /**
     * @return string
     */
    abstract public function name(): string ;

    /**
     *
     */
    public function render()
    {
        $html = '';
         collect($this->display())->each(function ($field) use (&$html){
            $html .= $field->form('filters')->render();
        });

        return $html;
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return count($this->display());
    }

    /**
     * @return bool
     */
    public function isApply() :bool
    {
        return count($this->request->only($this->parameters, [])) > 0;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        $params = $this->request->only($this->parameters, []);
        $values = collect($params)->flatten()->implode(',');

        return $this->name() . ': ' . $values;
    }

    /**
     * @return string
     */
    public function resetLink(): string
    {
        $params = $this->request->except($this->parameters);

        return url($this->request->url(), $params);
    }
}