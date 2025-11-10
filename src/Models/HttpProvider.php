<?php

namespace NckRtl\HttpManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NckRtl\HttpManager\Concerns\HasTeamScope;

/**
 * @property int $id
 * @property int|null $team_id
 * @property string $name
 * @property string $base_url
 * @property array $credential_config
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class HttpProvider extends Model
{
    use HasTeamScope;
    use SoftDeletes;

    protected $fillable = [
        'team_id',
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
