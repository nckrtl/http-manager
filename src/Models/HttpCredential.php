<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $http_provider_id
 * @property array $config
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class HttpCredential extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
