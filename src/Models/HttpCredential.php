<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
