<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HttpEndpoint extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
