<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NckRtl\HttpManager\Concerns\HasTeamScope;

/**
 * @property int $id
 * @property int|null $team_id
 * @property int $http_provider_id
 * @property string $name
 * @property string $method
 * @property string $endpoint
 * @property array|null $options
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class HttpEndpoint extends Model
{
    use HasTeamScope;
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'http_provider_id',
        'name',
        'method',
        'endpoint',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(HttpProvider::class, 'http_provider_id');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(HttpEndpointConfiguration::class);
    }
}
