# Mesour Editable

- [Documentation](http://components.mesour.com/component/editable)
- [Author](http://mesour.com)

# Install

- With [Composer](https://getcomposer.org)

        composer require mesour/editable

- Or download source from [GitHub](https://github.com/mesour/editable/releases)

# Tests

Before first run, create `config.local.php` file. Can use `config.php` as template.

Run command `vendor/bin/tester tests/ -s -c tests/php.ini --colors`

# Code style

Run command `vendor/bin/phpcs --standard=ruleset.xml --extensions=php,phpt --encoding=utf-8 --tab-width=4 -sp src tests`
