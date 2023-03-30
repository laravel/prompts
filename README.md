# Prompts

A PHP library for creating beautiful and user-friendly prompts for your command-line applications.

## Installation

```sh
composer require laravel/prompts
```

## Usage

### Text

```php
use function Laravel\Prompts\text;

$name = text('What is your name?');
```

You may also provide a placeholder, default value, and validation callback:

```php
use function Laravel\Prompts\text;

$name = text(
    message: 'What is your name?',
    placeholder: 'E.g. Taylor Otwell',
    default: $user->name,
    validate: function ($value) {
        if (! $value) {
            return 'Please enter your name.';
        }
    }
);
```

### Password

```php
use function Laravel\Prompts\password;

$password = password('What is the password?');
```

You may also provide a validation callback:

```php
use function Laravel\Prompts\password;

$password = password(
    message: 'What is the password?',
    validate: function ($value) {
        if (strlen($value) < 8) {
            return 'Password must have at least 8 characters.';
        }
    }
);
```

### Confirm

```php
use function Laravel\Prompts\confirm;

$confirmed = confirm('Do you wish to continue');
```

You may also provide a default value:

```php
use function Laravel\Prompts\confirm;

$confirmed = confirm(
    message: 'Do you wish to continue',
    default: false,
);
```

### Select

```php
use function Laravel\Prompts\select;

$role = select('What role should the user have?', [
    'Member',
    'Administrator',
    'Owner',
]);
```

You may also provide keys for each option, and a default value:

```php
use function Laravel\Prompts\select;

$role = select(
    message: 'What role should the user have?',
    options: [
        'member' => 'Member',
        'admin' => 'Administrator',
        'owner' => 'Owner',
    ]
    default: 'member',
);
```

### Multi-select

```php
use function Laravel\Prompts\multiselect;

$permissions = multiselect('What permissions should the user have?', [
    'Create',
    'Read',
    'Update',
    'Delete',
]);
```

You may also provide keys for each option, a default value, and a validation callback:

```php
use function Laravel\Prompts\multiselect;

$permissions = multiselect(
    message: 'What permissions should the user have?',
    options: [
        'create' => 'Create',
        'read' => 'Read',
        'update' => 'Update',
        'delete' => 'Delete',
    ]
    default: ['read'],
    validate: function ($values) {
        if (count($values) < 1) {
            return 'Please select at least 1 option.';
        }
    },
);
```

### Spinner

```php
use function Laravel\Prompts\spin;

$result = spin(function () {
    // Do something...
}, 'Doing something...');
```

> **Note** The spinner requires the `ext-pcntl` PHP extension to animate the spinner.

### Notes

There are several different note styles that can be rendered.

```php
use function Laravel\Prompts\note;

note('The command was successful.');
```

```php
use function Laravel\Prompts\error;

error('Something went wrong!');
```

```php
use function Laravel\Prompts\intro;

intro('Welcome');
```

```php
use function Laravel\Prompts\outro;

outro('Happy Coding!');
```

## Compatibility with Laravel's existing prompt methods

If you already have an Artisan console command that is using Laravel's [existing prompt methods](https://laravel.com/docs/artisan#prompting-for-input), you may update it to use Laravel Prompts with the `Laravel\Prompts\Prompts` trait:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Prompts\Prompts;

class GreetingCommand extends Command
{
    use Prompts;

    // ...

    public function handle(): void
    {
        $name = $this->ask('What is your name?');

        // ...
    }
}
```

> **Note** Some features are not available via the existing method API, such as placeholders and validation.

## Themes

Laravel Prompts comes with a beautiful default theme, but you are welcome to add your own.

Themes can be registered using the `addTheme` method on the `Prompt` class:

```php
use Laravel\Promts\Prompt;

Prompt::addTheme('clack', [
    \Laravel\Prompts\TextPrompt::class => \App\Console\Prompts\Themes\Clack\TextPromptRenderer::class,
    \Laravel\Prompts\PasswordPrompt::class => \App\Console\Prompts\Themes\Clack\PasswordPromptRenderer::class,
    \Laravel\Prompts\SelectPrompt::class => \App\Console\Prompts\Themes\Clack\SelectPromptRenderer::class,
    \Laravel\Prompts\MultiSelectPrompt::class => \App\Console\Prompts\Themes\Clack\MultiSelectPromptRenderer::class,
    \Laravel\Prompts\ConfirmPrompt::class => \App\Console\Prompts\Themes\Clack\ConfirmPromptRenderer::class,
    \Laravel\Prompts\Spinner::class => \App\Console\Prompts\Themes\Clack\SpinnerRenderer::class,
    \Laravel\Prompts\Note::class => \App\Console\Prompts\Themes\Clack\NoteRenderer::class,
]);
```

Take a look at the default theme provided by Laravel Prompts to see how it works.

The active theme can be changed using the `theme` method:

```php
use Laravel\Promts\Prompt;

Prompt::theme('clack');
```
