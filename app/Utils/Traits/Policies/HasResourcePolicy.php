<?php

namespace App\Utils\Traits\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasResourcePolicy
{
    /**
     * Get the table associated with the policy.
     *
     * @return string
     */
    public function getTable()
    {
        return Str::snake(Str::pluralStudly(Str::replaceLast('Policy', '', class_basename($this))));
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(Model $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(Model $user, Model $model)
    {
        return $user->can("view {$this->getTable()}");
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(Model $user)
    {
        return $user->can("create {$this->getTable()}");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(Model $user, Model $model)
    {
        return $user->can("update {$this->getTable()}");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(Model $user, Model $model)
    {
        return $user->can("delete {$this->getTable()}");
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(Model $user, Model $model)
    {
        return $user->can("restore {$this->getTable()}");
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(Model $user, Model $model)
    {
        return $user->can("force_delete {$this->getTable()}");
    }
}
