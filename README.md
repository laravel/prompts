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

<img src="https://user-images.githubusercontent.com/4977161/229037808-98b247cc-e862-4f97-a581-5a16ffbabc65.gif" width="830" />

Prompt the user for text with an optional placeholder, default value, and validation.

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

<img src="https://user-images.githubusercontent.com/4977161/229038919-626d2a14-36b5-4f28-9957-e4c861edb436.gif" width="830" />

Prompt the user for text while masking their input.

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

<img src="https://user-images.githubusercontent.com/4977161/229039659-fdece306-7a56-4142-9c5b-6574282c291d.gif" width="830" />

Prompt the user for a yes or no answer.

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

<img src="https://user-images.githubusercontent.com/4977161/229040185-dcf6e1ee-f9e0-414a-9771-cf52d378eb2c.gif" width="830" />

Prompt the user to select an option.

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

<img src="https://user-images.githubusercontent.com/4977161/229041010-5174786c-6301-4a05-b296-aca7fd489b5f.gif" width="830" />

Prompt the user to select multiple options.

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

### Auto-completion

<img src="https://user-images.githubusercontent.com/4977161/229041595-808bc3f2-2d23-4ffd-8104-f99574c5aa8f.gif" width="830" />

Prompt the user for text with a list of suggested options that can be scrolled through or tab completed.

```php
use function Laravel\Prompts\anticipate;

$color = anticipate('What is your favorite color', [
    'Red',
    'Green',
    'Blue',
]);
```

You may also provide keys a placeholder, default value, and a validation callback:

```php
use function Laravel\Prompts\anticipate;

$color = anticipate(
    message: 'What is your favorite color?',
    placeholder: 'Enter any color your like!',
    options: [
        'Red',
        'Green',
        'Blue',
    ]
    default: 'Red'
    validate: function ($value) {
        if (strlen($value) < 1) {
            return 'Please enter a color';
        }
    },
);
```

By default, options are matched based on whether they start with the users input in a case insensitive manner. You may provide a callback function to control what matches are provided:

```php
use function Laravel\Prompts\anticipate;

$color = anticipate(
    message: 'What is your favorite color?',
    options: fn (string $value) => array_filter(
        [
            'Red',
            'Green',
            'Blue',
        ],
        fn ($option) => str_contains(strtolower($option), strtolower($value)),
    ),
);
```

### Spinner

<img src="https://user-images.githubusercontent.com/4977161/229042721-926b09ee-2784-4aed-9f66-f09114004bb0.gif" width="830" />

Render a spinner while the provided callback runs.

> **Note** The spinner requires the `ext-pcntl` PHP extension to animate the spinner, otherwise a static version will be rendered instead.

```php
use function Laravel\Prompts\spin;

$result = spin(function () {
    sleep(3);

    return 'Result';
}, 'Doing something...');
```

### Notes

<img src="https://user-images.githubusercontent.com/4977161/229044919-584fcbfe-5346-4a92-9e95-4b7e9ca31e86.gif" width="830" />

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
    \Laravel\Prompts\AnticipatePrompt::class => \App\Console\Prompts\Themes\Clack\AnticipatePromptRenderer::class,
    \Laravel\Prompts\ConfirmPrompt::class => \App\Console\Prompts\Themes\Clack\ConfirmPromptRenderer::class,
    \Laravel\Prompts\MultiSelectPrompt::class => \App\Console\Prompts\Themes\Clack\MultiSelectPromptRenderer::class,
    \Laravel\Prompts\Note::class => \App\Console\Prompts\Themes\Clack\NoteRenderer::class,
    \Laravel\Prompts\PasswordPrompt::class => \App\Console\Prompts\Themes\Clack\PasswordPromptRenderer::class,
    \Laravel\Prompts\SelectPrompt::class => \App\Console\Prompts\Themes\Clack\SelectPromptRenderer::class,
    \Laravel\Prompts\Spinner::class => \App\Console\Prompts\Themes\Clack\SpinnerRenderer::class,
    \Laravel\Prompts\TextPrompt::class => \App\Console\Prompts\Themes\Clack\TextPromptRenderer::class,
]);
```

Take a look at the default theme provided by Laravel Prompts to see how it works.

The active theme can be changed using the `theme` method:

```php
use Laravel\Promts\Prompt;

Prompt::theme('clack');
```
