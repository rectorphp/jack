# Raise your Dependency Versions with Jack

**Experimental**: Jack is an experimental project under active development. It is not yet stable, may contain bugs or undergo breaking changes. It's build it in the open with the community feedback.

[![Downloads total](https://img.shields.io/packagist/dt/rector/jack.svg?style=flat-square)](https://packagist.org/packages/rector/jack/stats)

<br>

<img src="/docs/jack.jpg" alt="Jack" width="300" align="center">

<br>

The slow and steady way to raise your `composer.json` dependencies versions.

No more outdated dependencies without noticing.

<br>

## Install

```bash
composer require rector/jack --dev
```

<br>

## Usage

<br>

## 1. Too many outdated dependencies? Let your CI tell you

We tend to postpone upgrade and to them in big jumps = once a couple years. The postponing turns upgrades to harder and more costly project. Also, we can face more errors, as some newer version of packages no longer work with our PHP version.

Let CI pay attention to this issue for us.

Too many outdated major packages? CI will fail.

```bash
vendor/bin/jack breakpoint
```

<br>

Default limit of outdated packages is 5.

Do you 15 outdated packages? Make it fit your needs - goal of this command is not to get stressed, but **to keep raising your dependencies one step at a time**:

```bash
vendor/bin/jack breakpoint --limit 13
```

This way, the upgrade will be come to our focus, if we're lacking behind for way too long.
No more: "oops, all our dependencies are 3 years old, let's update them all at once" mayhem.

<br>

## 2. Open up next versions

We know we're behind the latest versions of our dependencies, but where to start? Which versions should be force to update first? We can get lot of errors if we try to bump wrong end of not.

Instead, let composer handle it. How? We open-up package versions to the next version:

```diff
 {
     "require": {
         "php": "^7.4",
-            "symfony/console": "^5.0"
+            "symfony/console": "^5.0|^6.0"
         },
         "require-dev": {
-            "phpunit/phpunit": "^9.0"
+            "phpunit/phpunit": "^10.0"
         }
     }
 }
```

Not forcing, just opening up. If composer won't see any blockers, it will update the package to the next version.

<br>

You can limit the range of versions to open up by using the `--limit` option (default 5)

```bash
vendor/bin/jack open-versions --limit 3
```

<br>

To try it out without changing the `composer.json`, you can use the `--dry-run` option.

```bash
vendor/bin/jack open-versions --dry-run
```

<br>

It's proven practice to update all dev packages first, as they're safer low hanging fruit:

```bash
vendor/bin/jack open-versions --dev
```

This way we **get slowly and steadily to the next possible version** of our dependencies.

<br>

Happy coding!
