# Release Notes

## [Unreleased](https://github.com/laravel/prompts/compare/v0.1.11...main)

## [v0.1.11](https://github.com/laravel/prompts/compare/v0.1.10...v0.1.11) - 2023-10-03

- Adds support for alternate HOME and END escape keys by [@TWithers](https://github.com/TWithers) in https://github.com/laravel/prompts/pull/87

## [v0.1.10](https://github.com/laravel/prompts/compare/v0.1.9...v0.1.10) - 2023-09-29

- Prioritize fallback over non-interactive mode by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/91

## [v0.1.9](https://github.com/laravel/prompts/compare/v0.1.8...v0.1.9) - 2023-09-26

- Register shutdown function to kill process in the event of an early exit by [@joetannenbaum](https://github.com/joetannenbaum) in https://github.com/laravel/prompts/pull/76
- Add non-interactive mode by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/73
- Add `multisearch` prompt by [@irobin591](https://github.com/irobin591) in https://github.com/laravel/prompts/pull/58
- Allow to target `^0.2` before official release by [@crynobone](https://github.com/crynobone) in https://github.com/laravel/prompts/pull/78
- Allow `select` and `search` "required" message to be customized. by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/84
- Replace shutdown handlers with destructors by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/81
- Update dependencies for 0.1.9 release by [@crynobone](https://github.com/crynobone) in https://github.com/laravel/prompts/pull/83
- Add Progress Bar Helper by [@joetannenbaum](https://github.com/joetannenbaum) in https://github.com/laravel/prompts/pull/82

## [v0.1.8](https://github.com/laravel/prompts/compare/v0.1.7...v0.1.8) - 2023-09-19

- Add a `table` renderer by [@joetannenbaum](https://github.com/joetannenbaum) in https://github.com/laravel/prompts/pull/68
- Support emacs style key binding  by [@storyn26383](https://github.com/storyn26383) in https://github.com/laravel/prompts/pull/67
- Support more readline keys by [@storyn26383](https://github.com/storyn26383) in https://github.com/laravel/prompts/pull/70
- `spin`: Ensure process termination on `$callback()` failure by [@manelgavalda](https://github.com/manelgavalda) in https://github.com/laravel/prompts/pull/71

## [v0.1.7](https://github.com/laravel/prompts/compare/v0.1.6...v0.1.7) - 2023-09-12

- Make adjusted `scroll` value available in prompt constructor by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/60
- Display count of selected items when scrolling by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/61
- fix(select): default value not being centered when not visible by [@toyi](https://github.com/toyi) in https://github.com/laravel/prompts/pull/59
- Use fallback implementation when `stty` command fails by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/63
- Fix synchronous rendering of spinner by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/66

## [v0.1.6](https://github.com/laravel/prompts/compare/v0.1.5...v0.1.6) - 2023-08-18

- Fix display of submitted multiselect selections by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/53
- Add a default hint for the `multiselect` prompt by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/50
- Remove redundant handling of Symfony-style tags by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/49
- Add view state for scroll prompts by [@crazywhalecc](https://github.com/crazywhalecc) in https://github.com/laravel/prompts/pull/43

## [v0.1.5](https://github.com/laravel/prompts/compare/v0.1.4...v0.1.5) - 2023-08-15

- Correct initial release version in changelog by [@ivacuum](https://github.com/ivacuum) in https://github.com/laravel/prompts/pull/46
- Draw Boxes With All Supported Colors by [@devajmeireles](https://github.com/devajmeireles) in https://github.com/laravel/prompts/pull/47
- Prompt `hint` by [@devajmeireles](https://github.com/devajmeireles) in https://github.com/laravel/prompts/pull/44

## [v0.1.4](https://github.com/laravel/prompts/compare/v0.1.3...v0.1.4) - 2023-08-07

- Update README.md by [@lazer-hybiz](https://github.com/lazer-hybiz) in https://github.com/laravel/prompts/pull/35
- Don't allow negativ str_repeats by [@mpociot](https://github.com/mpociot) in https://github.com/laravel/prompts/pull/37
- Prevent C0 control codes by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/38
- support mutli-byte characters by [@tyler36](https://github.com/tyler36) in https://github.com/laravel/prompts/pull/33
- Add support for alternative arrow keys by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/40
- Add info note by [@sebastianpopp](https://github.com/sebastianpopp) in https://github.com/laravel/prompts/pull/36

## [v0.1.3](https://github.com/laravel/prompts/compare/v0.1.2...v0.1.3) - 2023-08-02

No major changes.

## [v0.1.2](https://github.com/laravel/prompts/compare/v0.1.1...v0.1.2) - 2023-08-02

- added null type hints by [@oreillysean](https://github.com/oreillysean) in https://github.com/laravel/prompts/pull/27
- Fix required validation on `suggest` prompt by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/25
- Improve support for accent characters by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/26
- Fix output of escape characters when using `NullOutput` interface by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/28

## [v0.1.1](https://github.com/laravel/prompts/compare/v1.0.0...v0.1.1) - 2023-07-31

- (feat) Enforce static calls of static methods by [@lucasmichot](https://github.com/lucasmichot) in https://github.com/laravel/prompts/pull/11
- fix: Check for non-null return value from fread to handle character z… by [@igorblumberg](https://github.com/igorblumberg) in https://github.com/laravel/prompts/pull/15
- Replace tput command as it's not available on all OSes by [@digiservnet](https://github.com/digiservnet) in https://github.com/laravel/prompts/pull/14
- Improve errors on unsupported environments by [@jessarcher](https://github.com/jessarcher) in https://github.com/laravel/prompts/pull/20

## v0.1.0 (2023-07-26)

Initial release.
