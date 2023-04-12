# Prompts

A PHP library for creating beautiful and user-friendly prompts for your command-line applications.

## Installation

> **Note** These are pre-release instructions

In the parent directory of your application:

```
git clone git@github.com:laravel-labs/prompts.git
cd <your-application>
composer config minimum-stability dev
composer config repositories.prompts '{"type": "path", "url": "../prompts"}'

composer require laravel/prompts
```

## Usage

### Text

<img src="https://user-images.githubusercontent.com/4977161/229967783-61e76980-0136-4741-981e-cbd76ebb2d0d.gif" width="830" />

Prompt the user for text with an optional placeholder, default value, and validation.

```php
use function Laravel\Prompts\text;

$name = text('What is your email address?');
```

You may also provide a placeholder, default value, and validation callback:

```php
use function Laravel\Prompts\text;

$name = text(
    label: 'What is your email address?',
    placeholder: 'E.g. taylor@laravel.com',
    default: $user->email,
    validate: fn ($value) => match (true) {
        strlen($value) === 0 => 'Please enter an email address.',
        ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'Please enter a valid email address.',
        default => null,
    },
);
```

### Password

<img src="https://user-images.githubusercontent.com/4977161/229968130-3509a96b-8f8a-4703-b6b5-09c487fc1ef0.gif" width="830" />

Prompt the user for text while masking their input.

```php

use function Laravel\Prompts\password;

$password = password('Please provide a password');
```

You may also provide a validation callback:

```php
use function Laravel\Prompts\password;

$password = password(
    label: 'Please provide a password',
    validate: function ($value) {
        if (strlen($value) < 8) {
            return 'Password must have at least 8 characters.';
        }
    },
);
```

### Confirm

<img src="https://user-images.githubusercontent.com/4977161/229968418-7ad624d1-4bb4-44bc-b61b-e70f9c5461c6.gif" width="830" />

Prompt the user for a yes or no answer.

```php
use function Laravel\Prompts\confirm;

$confirmed = confirm('Would you like to install dependencies?');
```

You may also provide a default value, alternative labels for 'Yes' and 'No', and a validation callback:

```php
use function Laravel\Prompts\confirm;

$confirmed = confirm(
    label: 'Would you like to install dependencies?',
    default: false,
    yes: 'Yes, please',
    no: 'No, thank you',
    validate: fn ($value) => $value !== true ? 'You must select yes.' : null,
);
```

### Select

<img src="https://user-images.githubusercontent.com/4977161/229968732-d40172f4-cf8e-47e2-8d54-54cc3a5015fc.gif" width="830" />

Prompt the user to select an option.

```php
use function Laravel\Prompts\select;

$role = select('What role should the user have?', [
    'Member',
    'Contributor',
    'Owner',
]);
```

You may also provide keys for each option, a default value, scroll configuration, and a validation callback:

```php
use function Laravel\Prompts\select;

$role = select(
    label: 'What role should the user have?',
    options: [
        'member' => 'Member',
        'contributor' => 'Contributor',
        'owner' => 'Owner',
    ],
    default: 'member',
    scroll: 10,
    validate: fn ($value) => $value === 'owner' ? 'There are too many owners already.' : null,
);
```

### Multi-select

<img src="https://user-images.githubusercontent.com/4977161/229969042-f84d8709-a98c-46f1-ac34-2f7c3c6f72fd.gif" width="830" />

Prompt the user to select multiple options.

```php
use function Laravel\Prompts\multiselect;

$permissions = multiselect('What permissions should the user have?', [
    'View',
    'Create',
    'Update',
    'Delete',
]);
```

You may also provide keys for each option, a default value, scroll configuration, and a validation callback:

```php
use function Laravel\Prompts\multiselect;

$permissions = multiselect(
    label: 'What permissions should the user have?',
    options: [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
    ],
    default: ['read'],
    scroll: 10,
    validate: function ($values) {
        if (count($values) < 1) {
            return 'Please select at least 1 option.';
        }
    },
);
```

### Auto-completion

<img src="https://user-images.githubusercontent.com/4977161/229969640-d397f75f-286f-41d9-aab6-bcd381b262bc.gif" width="830" />

Prompt the user for text with a list of suggested options that can be scrolled through or tab completed.

```php
use function Laravel\Prompts\suggest;

$model = suggest('What model should the policy apply to?', [
    'Article',
    'Destination',
    'Flight',
]);
```

You may also provide keys a placeholder, default value, scroll configuration, and a validation callback:

```php
use function Laravel\Prompts\suggest;

$model = suggest(
    label: 'What model should the policy apply to?',
    placeholder: 'E.g. User',
    options: [
        'Article',
        'Destination',
        'Flight',
    ],
    default: 'Article',
    scroll: 10,
    validate: function ($value) {
        if (strlen($value) < 1) {
            return 'Please enter a model name.';
        }
    },
);
```

By default, options are matched based on whether they start with the users input in a case insensitive manner. You may provide a callback function to control what matches are provided:

```php
use function Laravel\Prompts\suggest;

$model = suggest(
    label: 'What model should the policy apply to?',
    options: fn (string $value) => array_filter(
        [
            'Article',
            'Destination',
            'Flight',
        ],
        fn ($option) => str_contains(strtolower($option), strtolower($value)),
    ),
);
```

### Spinner

<img src="https://user-images.githubusercontent.com/4977161/229970548-0c3a931c-a7e7-4432-9930-08cebbf80b7a.gif" width="830" />

Render a spinner while a callback runs.

> **Note** The spinner requires the `ext-pcntl` PHP extension to animate the spinner, otherwise a static version will be rendered instead.

```php
use function Laravel\Prompts\spin;

$result = spin(function () {
    sleep(3);

    return 'Result';
}, 'Installing dependencies...');
```

### Notes

<img src="https://user-images.githubusercontent.com/4977161/229971106-9cc05dff-1d2b-4114-a9c1-702424ff9aee.gif" width="830" />

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
use function Laravel\Prompts\warning;

warning('Something went wrong!');
```

```php
use function Laravel\Prompts\alert;

alert('Something went wrong!');
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
    \Laravel\Prompts\ConfirmPrompt::class => \App\Console\Prompts\Themes\Clack\ConfirmPromptRenderer::class,
    \Laravel\Prompts\MultiSelectPrompt::class => \App\Console\Prompts\Themes\Clack\MultiSelectPromptRenderer::class,
    \Laravel\Prompts\Note::class => \App\Console\Prompts\Themes\Clack\NoteRenderer::class,
    \Laravel\Prompts\PasswordPrompt::class => \App\Console\Prompts\Themes\Clack\PasswordPromptRenderer::class,
    \Laravel\Prompts\SelectPrompt::class => \App\Console\Prompts\Themes\Clack\SelectPromptRenderer::class,
    \Laravel\Prompts\Spinner::class => \App\Console\Prompts\Themes\Clack\SpinnerRenderer::class,
    \Laravel\Prompts\SuggestPrompt::class => \App\Console\Prompts\Themes\Clack\SuggestPromptRenderer::class,
    \Laravel\Prompts\TextPrompt::class => \App\Console\Prompts\Themes\Clack\TextPromptRenderer::class,
]);
```

Take a look at the default theme provided by Laravel Prompts to see how it works.

The active theme can be changed using the `theme` method:

```php
use Laravel\Promts\Prompt;

Prompt::theme('clack');
```
