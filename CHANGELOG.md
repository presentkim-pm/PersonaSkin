# Changelog

## 2.0.0 (2026-11-27)

The Poggit plugin version for the original [pmmp/PocketMine-MP](https://github.com/pmmp/PocketMine-MP) 5.x is kept on the
[`legacy/pmmp-5.44`](https://github.com/presentkim-pm/PersonaSkin/tree/legacy/pmmp-5.44) branch.

### ⚠️ Breaking changes
- Targets [axolotl-pm/PocketMine-MP](https://github.com/axolotl-pm/PocketMine-MP) `5.51` (BedrockProtocol 64,
  Bedrock `1.26.60`) and **PHP 8.4+**.
- Distributed as a phar from GitHub Releases instead of Poggit.

### 🔄 Changes
- `composer.json` added (Composer is only used for development and testing); `plugin.yml` has
  `src-namespace-prefix`, and the sources moved from `src/kim/present/personaskin/` to `src/`.
- PHPUnit tests for `PersonaSkinAdapter` and a class load test.
- On disable, the original skin adapter is only restored if no other plugin has replaced this plugin's adapter.

### 🐛 Fixes
- `PersonaSkinAdapter` kept the persona skin data of every skin ever created, keyed by `spl_object_id()`. Object ids are
  reused after an object is freed, so a later skin could be sent with another player's persona data, and the data was
  never released. It is now stored in a `WeakMap` keyed by the `Skin`.
