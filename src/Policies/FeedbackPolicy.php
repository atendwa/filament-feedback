<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Policies;

use Atendwa\Feedback\Models\Feedback;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Model;

class FeedbackPolicy
{
    public function viewAny(Authorizable $user): bool
    {
        return $user->can($this->permission('ViewAny'));
    }

    public function view(Authorizable $user, Model $model): bool
    {
        return $user->can($this->permission('View'))
            || ($model instanceof Feedback && ! $model->is_anonymous && $model->user_id === $user->getAuthIdentifier());
    }

    public function respond(Authorizable $user): bool
    {
        return $user->can($this->permission('Respond'));
    }

    public function create(): bool
    {
        return false;
    }

    public function update(): bool
    {
        return false;
    }

    public function delete(Authorizable $user, Model $model): bool
    {
        return $user->can($this->permission('Delete')) && blank($model->getAttribute('deleted_at'));
    }

    public function deleteAny(Authorizable $user): bool
    {
        return $user->can($this->permission('DeleteAny'));
    }

    public function restore(Authorizable $user, Model $model): bool
    {
        return $user->can($this->permission('Restore')) && filled($model->getAttribute('deleted_at'));
    }

    public function restoreAny(Authorizable $user): bool
    {
        return $user->can($this->permission('RestoreAny'));
    }

    public function forceDelete(Authorizable $user, Model $model): bool
    {
        return $user->can($this->permission('ForceDelete')) && filled($model->getAttribute('deleted_at'));
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can($this->permission('ForceDeleteAny'));
    }

    private function permission(string $ability): string
    {
        return $ability . ':Feedback';
    }
}
