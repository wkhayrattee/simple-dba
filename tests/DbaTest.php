<?php
/**
 * SetUp
 */
beforeEach(function () {
    /** @var \SimpleDba\Dba */
    $this->dbaObject = getDbaObject();
});

/**
 * Testing Constructor
 */
test('Constructor has set path to: "/var/www/storage"', function () {
    expect($this->dbaObject->path)->toBe('/var/www/storage');
});

test('Constructor has set store_name to: "greetings_store"', function () {
    expect($this->dbaObject->store_name)->toBe('greetings_store');
});

test('Constructor has set handle_name to: "lmdb"', function () {
    expect($this->dbaObject->handler_name)->toBe('lmdb');
});

it('has also set the full path to: "/var/www/storage/greetings_store"', function () {
    expect($this->dbaObject->full_path)->toBe('/var/www/storage/greetings_store');
});

/**
 * Testing Open() and Open Persistent
 */
test('open() - returned a resource handle to an opened connection to dba', function () {
    expect($this->dbaObject->open())->toBeResource();
});

test('open_persistent() - returned a resource handle to a persistent opened connection to dba', function () {
    expect($this->dbaObject->open_persistent())->toBeResource();
});

/**
 * Testing Close()
 */
test('Close() - When Closing the connection, the handler is indeed null', function () {
    $this->dbaObject->open();
    $this->dbaObject->close();
    expect($this->dbaObject->resource_handle)->toBeNull();
});

/**
 * Testing fetch()
 */
test('fetch() - retrieval of a value "hello world" for the key "123" should be successful', function () {
    $this->dbaObject->open();
    $this->dbaObject->insert('123', 'hello world');
    expect($this->dbaObject->fetch('123'))->toEqual('hello world');
});
test('fetch() - retrieval of an unknown key "123x" should return false', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->fetch('123x'))->toBeFalse();
});

/**
 * Testing insert()
 */
test('insert() - Insertion of a key "any_key_name", with value "any value" - should return TRUE', function () {
    $this->dbaObject->open();
    $this->dbaObject->insert('any_key_name', 'any value');
    expect($this->dbaObject->exists('any_key_name'))->toBeTrue();
});
it('seems to have been inserted, as the key "any_key_name" is present', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->exists('any_key_name'))->toBeTrue();
});
it('was indeed well inserted, as the key "any_key_name" has the intended value "any value"', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->fetch('any_key_name'))->toEqual('any value');
});
test('insert() - Another insertion of the existing key "any_key_name", with another value "an updated value" - should fail, returning FALSE', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->insert('any_key_name', 'an updated value'))->toBeFalse();
});
it('confirms the original inserted value is still "any value"', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->fetch('any_key_name'))->toEqual('any value');
});

/**
 * Testing exists()
 */
test('exists() - Checking for existence of the stored key "7" - should return TRUE', function () {
    $this->dbaObject->open();
    $this->dbaObject->insert('7', 'hello world, this is 7PHP');
    expect($this->dbaObject->exists('7'))->toBeTrue();
});

test('exists() - Checking for existence of the unknown key "777" - should return FALSE', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->exists('777'))->toBeFalse();
});

/**
 * Testing delete()
 */
test('delete() - Inserting key "2021" and deleting it - successful deletion should return TRUE', function () {
    $this->dbaObject->open();
    $this->dbaObject->insert('2021', 'hello world, this is 7PHP 2021');
    expect($this->dbaObject->delete('2021'))->toBeTrue();
});
it('also allows for Insertion of a key string "this_is_a_key" and deleting it - successful deletion should return TRUE', function () {
    $this->dbaObject->open();
    $this->dbaObject->insert('this_is_a_key', 'hello world, this is 7PHP');
    expect($this->dbaObject->delete('this_is_a_key'))->toBeTrue();
});
test('delete() - Deleting an unknown key "this_is_not_a_saved_key" - should return FALSE', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->delete('this_is_not_a_saved_key'))->toBeFalse();
});

/**
 * Tear down
 */
afterAll(function () {
    if (file_exists(getDbaObject()->full_path)) {
        rmdirRecursively(getDbaObject()->path);
    }
});
