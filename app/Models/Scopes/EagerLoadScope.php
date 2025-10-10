<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EagerLoadScope implements Scope
{
    protected array $relationships;

    public function __construct(array $relationships)
    {
        $this->relationships = $relationships;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with($this->relationships);
    }
}