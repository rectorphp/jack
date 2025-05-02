# Jack: Raise your Dependency Versions

**Experimental**: Jack is an experimental project under active development. It is not yet stable, may contain bugs or undergo breaking changes. It's build it in the open with the community feedback.

[![Downloads total](https://img.shields.io/packagist/dt/rector/jack.svg?style=flat-square)](https://packagist.org/packages/rector/jack/stats)

<br>

<img src="/docs/jack.jpg" alt="Jack" width="300" align="center">

<br>

Jack helps you incrementally update your `composer.json` dependencies, ensuring your project stays current without the chaos of outdated packages.

Say goodbye to unnoticed, years-old dependencies!

<br>

## Install

```bash
composer require rector/jack --dev
```

<br>

## Why Jack?

Manually upgrading dependencies can be daunting, especially when tackling multiple outdated packages at once. Large upgrades often lead to errors, compatibility issues, and costly delays. Jack automates and simplifies this process by:

- Monitoring outdated dependencies via CI.
- Gradually opening up package versions for safe updates.
- Prioritizing low-risk updates (e.g., dev dependencies).

With Jack, you upgrade **slowly and steadily**, avoiding the stress of massive, error-prone dependency overhauls.

<br>

## Usage

Jack offers two powerful commands to keep your dependencies up to date:

<br>

### 1. Too many outdated dependencies? Let your CI tell you

Postponing upgrades often results in large, risky jumps (e.g., updating every few years). Jack integrates with your CI pipeline to catch outdated dependencies early.

Run the `breakpoint` command to check for outdated major packages:

```bash
vendor/bin/jack breakpoint
```

By default, CI fails if there are more than **5 outdated packages**. Customize this limit to suit your project’s needs:

```bash
vendor/bin/jack breakpoint --limit 3
```

This ensures upgrades stay on your radar without overwhelming you. No more "oops, our dependencies are three years old" moments!

<br>

### 2. Open up Next Versions

We know we're behind the latest versions of our dependencies, but where to start? Which versions should be force to update first? We can get lot of errors if we try to bump wrong end of knot.

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
+            "phpunit/phpunit": "^9.0|^10.0"
         }
     }
 }
```

This "opens up" versions without forcing updates. If no blockers exist, Composer will upgrade to the next version.

<br>

Command Options:

**Limit the number of packages** to process (default: 5):

```bash
vendor/bin/jack open-versions --limit 3
```

<br>

**Dry run** to preview changes without modifying `composer.json`:

```bash
vendor/bin/jack open-versions --dry-run
```

<br>

**Update dev dependencies first** for safer, low-risk updates:

```bash
vendor/bin/jack open-versions --dev
```

This approach ensures you **progress steadily** toward the latest dependency versions.


<br>

Happy coding!
