<?php

namespace NckRtl\HttpManager\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasTeamScope
{
    /**
     * Boot the HasTeamScope trait for a model.
     */
    protected static function bootHasTeamScope(): void
    {
        // Only apply global scope if team scoping is enabled
        if (! config('http-manager.teams.enabled', false)) {
            return;
        }

        // Auto-add team_id on create if not set
        static::creating(function ($model) {
            // @phpstan-ignore property.notFound, method.notFound
            if (! isset($model->team_id)) {
                // @phpstan-ignore property.notFound, method.notFound
                $model->team_id = $model->getCurrentTeamId();
            }
        });

        // Apply global scope to filter by team_id
        static::addGlobalScope('team', function (Builder $builder) {
            $model = $builder->getModel();

            // @phpstan-ignore method.notFound
            $teamId = $model->getCurrentTeamId();
            if ($teamId) {
                $builder->where($builder->getModel()->getTable().'.team_id', $teamId);
            }
        });
    }

    /**
     * Get the current team ID.
     *
     * This method should be overridden by the application or set via config.
     */
    public function getCurrentTeamId(): ?int
    {
        // Try to get from authenticated user's current team
        $user = auth()->user();
        if ($user && method_exists($user, 'currentTeam')) {
            /** @phpstan-ignore property.notFound */
            $currentTeam = $user->currentTeam;
            if ($currentTeam && isset($currentTeam->id)) {
                return $currentTeam->id;
            }
        }

        // Fallback to a custom team resolver if configured
        $resolver = config('http-manager.teams.team_resolver');
        if ($resolver && is_callable($resolver)) {
            return $resolver();
        }

        return null;
    }

    /**
     * Query without the team scope.
     */
    public function scopeWithoutTeamScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('team');
    }

    /**
     * Query for a specific team.
     */
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->withoutGlobalScope('team')->where('team_id', $teamId);
    }

    /**
     * Query for all teams.
     */
    public function scopeForAllTeams(Builder $query): Builder
    {
        return $query->withoutGlobalScope('team');
    }
}
