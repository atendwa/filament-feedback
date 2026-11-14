# Filament Feedback

[![Latest Version](https://img.shields.io/packagist/v/atendwa/filament-feedback)](https://packagist.org/packages/atendwa/filament-feedback)
[![PHP](https://img.shields.io/packagist/dependency-v/atendwa/filament-feedback/php)](composer.json)
[![Laravel](https://img.shields.io/packagist/dependency-v/atendwa/filament-feedback/illuminate%2Fsupport?label=laravel)](composer.json)
[![Filament](https://img.shields.io/packagist/dependency-v/atendwa/filament-feedback/filament%2Ffilament?label=filament)](composer.json)
[![Tests](https://github.com/atendwa/filament-feedback/actions/workflows/tests.yml/badge.svg?branch=develop)](https://github.com/atendwa/filament-feedback/actions/workflows/tests.yml)

Feedback for Filament applications, in three parts:

- **A public form** at `/give-feedback` that anyone can use, signed in or not. People can stay anonymous (they are shown under a
  generated nickname), and can choose to get replies by email through a private, signed link to the conversation.
- **A staff inbox** (Filament resource) to read feedback, reply, add internal notes, resolve with a closing comment and reopen.
- **"My feedback"** (Filament resource) where signed-in people see what they sent under their name and continue the conversation.

Filament 5, Laravel 12 or 13, PHP 8.3+.

## Installation

```bash
composer require atendwa/filament-feedback
php artisan migrate
```

Register the plugin on your panels. Typically the inbox goes on the staff panel and "My feedback" on the panel everyone uses:

```php
use Atendwa\Feedback\Filament\FeedbackPlugin;

$panel->plugins([
    FeedbackPlugin::make()->inboxOnly(),   // staff panel
]);

$panel->plugins([
    FeedbackPlugin::make()->mineOnly()->loginLink(),   // everyone's panel; loginLink() adds "Give feedback" under the login form
]);
```

`FeedbackPlugin` options: `inboxOnly()`, `mineOnly()`, `loginLink()`, `cluster(SomeCluster::class)`, `navigationGroup('Support')`, `sort(10)`.

## Permissions

The inbox is guarded by a policy that checks these abilities with `$user->can(...)`, so any authorization setup works
(Filament Shield, spatie/laravel-permission, or your own gates):

| Ability | Allows |
|---|---|
| `ViewAny:Feedback` | open the inbox |
| `View:Feedback` | open any feedback (people can always open their own named feedback) |
| `Respond:Feedback` | reply, add notes, resolve and reopen |

If your user model uses spatie/laravel-permission, everyone with `Respond:Feedback` is also alerted (in the app and by email)
about new feedback and replies.

## Configuration

Values can be set in a `config/feedback.php` of your own or at runtime with `config([...])`.

| Key | Default | Meaning |
|---|---|---|
| `feedback.categories` | six general topics | choices on the form |
| `feedback.notify_email` | `env('FEEDBACK_NOTIFY_EMAIL')` | comma-separated addresses alerted about new feedback and replies |
| `feedback.public_limit` | `5` | submissions per hour from one address |
| `feedback.path` | `give-feedback` | URL of the public form |
| `feedback.user_model` | the `users` auth provider model | model that owns named feedback |
| `feedback.tenant` | `null` | callable returning the tenant for links in alert emails (multi-tenant panels) |
| `feedback.navigation.*` | | cluster, group and sort of the inbox and "My feedback" |
| `feedback.public.vite` | `null` | Vite entry (string or array) loaded on the public page, for your theme |
| `feedback.public.head` | `null` | Blade component rendered in the public page `<head>` (for example brand styles) |
| `feedback.public.logo` | `null` | logo path for the public page; the app name is shown when empty |
| `feedback.public.decoration` | `null` | Blade component shown beside the public form on large screens |

The user model needs `name` and `email` attributes; to receive in-app replies it should use Laravel's `Notifiable` trait.

## Testing

```bash
composer install
composer quality   # Pint, Rector, PHPStan (max), Pest
```

## License

MIT. See [LICENSE.md](LICENSE.md).
