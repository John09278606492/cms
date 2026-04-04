<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SitePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') && $user->can('ViewAny:Site');
    }

    public function view(User $user, Site $site): bool
    {
        if ($user->hasRole('super_admin')) {
            return $user->can('View:Site');
        }

        return $user->canAccessTenant($site);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'panel_user', 'site_owner']);
    }

    public function update(User $user, Site $site): bool
    {
        if ($user->hasRole('super_admin')) {
            return $user->can('Update:Site');
        }

        return $site->owner_id === $user->getKey();
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->hasRole('super_admin') && $user->can('Delete:Site');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('super_admin') && $user->can('DeleteAny:Site');
    }

    public function restore(User $user, Site $site): bool
    {
        return $user->hasRole('super_admin') && $user->can('Restore:Site');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasRole('super_admin') && $user->can('RestoreAny:Site');
    }

    public function forceDelete(User $user, Site $site): bool
    {
        return $user->hasRole('super_admin') && $user->can('ForceDelete:Site');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole('super_admin') && $user->can('ForceDeleteAny:Site');
    }

    public function replicate(User $user, Site $site): bool
    {
        return $user->hasRole('super_admin') && $user->can('Replicate:Site');
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole('super_admin') && $user->can('Reorder:Site');
    }
}
