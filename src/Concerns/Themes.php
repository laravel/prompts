<?php

namespace Laravel\Prompts\Concerns;

use InvalidArgumentException;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Note;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\Spinner;
use Laravel\Prompts\TextPrompt;
use Laravel\Prompts\Themes\Default\ConfirmPromptRenderer;
use Laravel\Prompts\Themes\Default\MultiSelectPromptRenderer;
use Laravel\Prompts\Themes\Default\NoteRenderer;
use Laravel\Prompts\Themes\Default\PasswordPromptRenderer;
use Laravel\Prompts\Themes\Default\SelectPromptRenderer;
use Laravel\Prompts\Themes\Default\SpinnerRenderer;
use Laravel\Prompts\Themes\Default\TextPromptRenderer;

trait Themes
{
    /**
     * The name of the active theme.
     *
     * @var string
     */
    protected static $theme = 'default';

    /**
     * The available themes.
     *
     * @var array<string, array<class-string, class-string>>
     */
    protected static $themes = [
        'default' => [
            TextPrompt::class => TextPromptRenderer::class,
            PasswordPrompt::class => PasswordPromptRenderer::class,
            SelectPrompt::class => SelectPromptRenderer::class,
            MultiSelectPrompt::class => MultiSelectPromptRenderer::class,
            ConfirmPrompt::class => ConfirmPromptRenderer::class,
            Spinner::class => SpinnerRenderer::class,
            Note::class => NoteRenderer::class,
        ]
    ];

    /**
     * Get or set the active theme.
     *
     * @param  string|null  $name
     * @return string|null
     */
    public static function theme($name = null)
    {
        if ($name === null) {
            return static::$theme;
        }

        if (! isset(static::$themes[$name])) {
            throw new InvalidArgumentException("Prompt theme [{$name}] not found.");
        }

        static::$theme = $name;
    }

    /**
     * Add a new theme.
     *
     * @param  string  $name
     * @param  array<class-string, class-string>  $renderers
     * @return void
     */
    public static function addTheme($name, $renderers)
    {
        if ($name === 'default') {
            throw new InvalidArgumentException('The default theme cannot be overridden.');
        }

        static::$themes[$name] = $renderers;
    }

    /**
     * Get the renderer for the current prompt.
     *
     * @return callable
     */
    protected function getRenderer()
    {
        $class = get_class($this);

        return new (static::$themes[static::$theme][$class] ?? static::$themes['default'][$class]);
    }

    /**
     * Render the prompt using the active theme.
     *
     * @return string
     */
    protected function renderTheme()
    {
        $renderer = $this->getRenderer();

        return $renderer($this);
    }
}
