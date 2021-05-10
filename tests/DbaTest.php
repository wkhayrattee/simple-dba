<?php

beforeEach(function () {
    /** @var \SimpleDba\Dba */
    $this->dbaObject = new \SimpleDba\Dba('/var/www/storage', 'lmdb');
});

test('Constructor has set path to: "/var/www/storage"', function () {
    expect($this->dbaObject->path)->toBe('/var/www/storage');
});

test('Constructor has set handle_name to: "lmdb"', function () {
    expect($this->dbaObject->handler_name)->toBe('lmdb');
});

it('returned a resource handle to an opened connection to dba', function () {
    expect($this->dbaObject->open())->toBeResource();
});

it('returned a resource handle to a persistent opened connection to dba', function () {
    expect($this->dbaObject->open_persistent())->toBeResource();
});

test('Closing the connection should return a null resource', function () {
    $this->dbaObject->open();
    $this->dbaObject->close();
    expect($this->dbaObject->resource_handle)->toBeNull();
});

