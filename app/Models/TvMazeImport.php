<?php

namespace App\Models;

use App\Enums\TvMazeImportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TvMazeImport extends Model
{
    use HasUuids;

    protected $table = 'tvmaze_imports';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'status',
        'current_page',
        'processed_pages',
        'processed_shows',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'status' => TvMazeImportStatus::class,
        'current_page' => 'integer',
        'processed_pages' => 'integer',
        'processed_shows' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            TvMazeImportStatus::PENDING->value,
            TvMazeImportStatus::RUNNING->value,
        ]);
    }
}
