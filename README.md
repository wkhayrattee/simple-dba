# Simple dba - a simple wrapper around the php dba functions

- While using the default dba functions provided by PHP, I have seen some inconsistencies and also wherever applicable, I would like to change certain things, specially the dba_replace..etc
- This is currently a work in progress, I am experimenting with one of my side projects - as soon as I find everything is OK, will publish v1.0.0

## TODO:
- Unit testing
- Get started with a simple example
- Publish in Packagist when ready

## Unit Testing

### Install PEST

I find PEST a little more elegant, let's try this.

```
$ composer require pestphp/pest --dev --with-all-dependencies
$ ./vendor/bin/pest --init
```

### Using PEST

```
$ ./vendor/bin/pest
```
