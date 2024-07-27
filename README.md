# Cepdtech_Pokemon module

Magento 2 module to integrate with PokeAPI

## ADR

### Write own Pokemon API client

There are 2 3rd party libraries, but both has problems:
- https://github.com/danrovito/pokephp
  - doesn't support injection of base URL
- https://github.com/lmerotta/phpokeapi
  - doesn't support PHP 8

In normal situation I will propose to use the first one (in the worst scenario (url changed) composer patch could be
used), but because this is skill presentation project, I decided to write my own client.

## Quality

Tested on:
- PHP 8.3
- Magento Community Edition 2.4.7

### Tests

```bash
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist vendor/cepdtech/pokemon
```

### Code sniffer
```bash
vendor/bin/phpcs --standard=Magento2 -p -s --colors --error-severity=1 --warning-severity=0 vendor/cepdtech/pokemon/
```

### Copy-paste detector
```bash
vendor/bin/phpcpd vendor/cepdtech/pokemon/
```

### Mess detector
```bash
vendor/bin/phpmd vendor/cepdtech/pokemon/ text dev/tests/static/testsuite/Magento/Test/Php/_files/phpmd/ruleset.xml
```
