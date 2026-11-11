<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Models;

use Atendwa\Feedback\Support\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $feedback_id
 * @property int|null $user_id
 * @property bool $is_from_submitter
 * @property bool $is_internal
 * @property string $body
 * @property Carbon $created_at
 */
class FeedbackComment extends Model
{
    protected $guarded = ['id'];

    /**
     * @return BelongsTo<Feedback, $this>
     */
    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::model(), 'user_id');
    }

    public function authorName(bool $forStaff): string
    {
        if ($this->is_from_submitter) {
            return $this->feedback?->submitterName() ?? 'Guest';
        }

        return $forStaff ? (Users::name($this->user) ?? 'Support') : 'Support team';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_from_submitter' => 'boolean', 'is_internal' => 'boolean'];
    }
}
