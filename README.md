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
