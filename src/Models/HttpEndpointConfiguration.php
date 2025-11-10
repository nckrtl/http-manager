<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $http_endpoint_id
 * @property int $http_credential_id
 * @property array $configuration
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class HttpEndpointConfiguration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'http_endpoint_id',
        'http_credential_id',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'encrypted:array',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(HttpEndpoint::class, 'http_endpoint_id');
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(HttpCredential::class, 'http_credential_id');
    }
}
