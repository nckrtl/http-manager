<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $base_url
 * @property array $credential_config
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class HttpProvider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'base_url',
        'credential_config',
    ];

    protected $casts = [
        'credential_config' => 'array',
    ];

    public function credentials(): HasMany
    {
        return $this->hasMany(HttpCredential::class);
    }

    public function endpoints(): HasMany
    {
        return $this->hasMany(HttpEndpoint::class);
    }
}
