<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Models;

use Atendwa\Feedback\Database\Factories\FeedbackFactory;
use Atendwa\Feedback\Enums\Status;
use Atendwa\Feedback\Policies\FeedbackPolicy;
use Atendwa\Feedback\Support\Alias;
use Atendwa\Feedback\Support\Users;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string|null $category
 * @property string $title
 * @property string $message
 * @property Status $status
 * @property int|null $user_id
 * @property bool $is_anonymous
 * @property string $alias
 * @property string|null $name
 * @property bool $wants_replies
 * @property string|null $contact_email
 * @property Carbon|null $last_activity_at
 * @property Carbon|null $closed_at
 * @property Carbon $created_at
 */
#[UseFactory(factoryClass: FeedbackFactory::class)]
#[UsePolicy(class: FeedbackPolicy::class)]
class Feedback extends Model
{
    /** @use HasFactory<FeedbackFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'feedback_entries';

    protected $guarded = ['id'];

    /**
     * @var list<string>
     */
    protected $hidden = ['user_id', 'contact_email'];

    /**
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::model(), 'user_id');
    }

    /**
     * @return HasMany<FeedbackComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(FeedbackComment::class)->oldest();
    }

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        $categories = array_values(array_filter(config()->array('feedback.categories'), is_string(...)));

        return array_combine($categories, $categories);
    }

    public function isNamedFor(int|string|null $userId): bool
    {
        return ! $this->is_anonymous && $this->user_id === $userId;
    }

    public function reference(): string
    {
        return 'FB-' . mb_str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function submitterName(): string
    {
        if ($this->is_anonymous) {
            return $this->alias;
        }

        return Users::name($this->user) ?? $this->name ?: 'Guest';
    }

    public function threadUrl(): string
    {
        return URL::signedRoute('feedback.thread', ['feedback' => $this->uuid]);
    }

    public function canBeRepliedTo(): bool
    {
        return $this->wants_replies;
    }

    public function touchActivity(): void
    {
        $this->forceFill(['last_activity_at' => now()])->saveQuietly();
    }

    protected static function booted(): void
    {
        static::creating(function (self $feedback): void {
            $feedback->uuid ??= (string) Str::uuid();
            $feedback->alias ??= Alias::for($feedback->uuid);
            $feedback->status ??= Status::Open;
            $feedback->last_activity_at ??= now();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'is_anonymous' => 'boolean',
            'wants_replies' => 'boolean',
            'contact_email' => 'encrypted',
            'last_activity_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
