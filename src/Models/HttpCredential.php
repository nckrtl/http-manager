<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NckRtl\HttpManager\Concerns\HasTeamScope;

/**
 * @property int $id
 * @property int $team_id
 * @property string $name
 * @property int $http_provider_id
 * @property array $config
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class HttpCredential extends Model
{
    use HasTeamScope;
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'http_provider_id',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(HttpProvider::class, 'http_provider_id');
    }

    public function endpointConfigurations(): HasMany
    {
        return $this->hasMany(HttpEndpointConfiguration::class);
    }
}
