# Jack: Raise your Dependency Versions

**Experimental**: Jack is an experimental project under active development. It is not yet stable, may contain bugs or undergo breaking changes. It's build it in the open with the community feedback.

[![Downloads total](https://img.shields.io/packagist/dt/rector/jack.svg?style=flat-square)](https://packagist.org/packages/rector/jack/stats)

<br>

In real world, "jack" is a tool that helps to raise your heavy car one inch at a time. So you can fix any issues down there and drive safely on journeys to come.

<br>

<img src="/docs/jack.jpg" alt="Jack" width="300" align="center">

<br>

In Composer world, Jack helps you to raise dependencies one version at a time, safely and steadily.

Say goodbye to unnoticed, years-old dependencies!

<br>

## Why Jack?

Manually upgrading dependencies can be daunting, especially when tackling multiple outdated packages at once. Large upgrades often lead to errors, compatibility issues, and costly delays.

Jack automates and simplifies this process by:

- Monitoring outdated dependencies via CI.
- Gradually opening up package versions for safe updates.
- Prioritizing low-risk updates (e.g., dev dependencies).

<br>

## Install

Rector Jack is downgraded and scoped. It requires **PHP 7.2+** and can be installed on any legacy project.

```bash
composer require rector/jack --dev
```

<br>

## Usage

Jack offers two powerful commands to keep your dependencies up to date:

<br>

### 1. Too many outdated dependencies? Let your CI tell you

Postponing upgrades often results in large, risky jumps (e.g., updating once a 3 years). Jack integrates with your CI pipeline to catch outdated dependencies early.

Run the `breakpoint` command to check for **outdated major packages**:

```bash
vendor/bin/jack breakpoint
```

↓

<img src="/docs/breakpoint-output.png" alt="Jack" width="400" align="center">


<br>

If there are more than 5 major outdated packages, the **CI will fail**.

<br>

Use `--limit` to raise or lower your bar:

```bash
vendor/bin/jack breakpoint --limit 3
```

This ensures upgrades stay on your radar without overwhelming you. No more "oops, our 30 dependencies are 5 years old" moments!

<br>

### 2. Open up Next Versions

We know we're behind the latest versions of our dependencies, but where to start? Which versions should be force to update first? We can get lot of conflicts if we try to bump wrong end of knot.

Instead, let Composer handle it. How? We open-up package versions to the next version:

```bash
vendor/bin/jack open-versions
```

This command opens up 5 versions to their next nearest step, e.g.:

```diff
 {
     "require": {
         "php": "^7.4",
-            "symfony/console": "5.1.*"
+            "symfony/console": "5.2.*|5.2.*"
         },
         "require-dev": {
-            "phpunit/phpunit": "^9.0"
+            "phpunit/phpunit": "^9.0|^10.0"
         }
     }
 }
```

Then we run Composer to do the work:

```bash
composer update
```

If no blockers exist, Composer will update packages to their next version.

<br>

To change the number of packages, use `--limit` option:

```bash
vendor/bin/jack open-versions --limit 3
```

<br>

To preview changes without modifying `composer.json`, use:

```bash
vendor/bin/jack open-versions --dry-run
```

<br>

Do you want to play it safe? Try low-risk dev packages first:

```bash
vendor/bin/jack open-versions --dev
```

<br>

Happy coding!
