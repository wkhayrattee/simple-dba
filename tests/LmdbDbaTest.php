<?php
/**
 * SetUp
 */
beforeEach(function () {
    /** @var \SimpleDba\Dba */
    $this->dbaObject = getLmdbDbaObject();
});

/**
 * Testing Constructor
 */
test('Constructor has set path to: "' . getDbaPath() . '"', function () {
    expect($this->dbaObject->path)->toBe(getDbaPath());
});

test('Constructor has set store_name to: "' . getLmdbStoreName() . '"', function () {
    expect($this->dbaObject->store_name)->toBe(getLmdbStoreName());
});

test('Constructor has set handle_name to: "lmdb"', function () {
    expect($this->dbaObject->handler_name)->toBe('lmdb');
});

it('has also set the full path to: "' . getDbaPath() . DIRECTORY_SEPARATOR . getLmdbStoreName() . '"', function () {
    expect($this->dbaObject->full_path)->toBe(getDbaPath() . DIRECTORY_SEPARATOR . getLmdbStoreName());
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
 * Testing createPathIfNone()
 */
test('createPathIfNone() - checking if the existing path "/var/www/storage" is there - returns TRUE', function () {
    $newObject = getLmdbDbaObject();
    $newObject->path = '/var/www/storage';
    expect($newObject->createPathIfNone())->toBeTrue();
});
test('createPathIfNone() - will attempt to create unknown path "/var/www/storagexxx" and on success returns TRUE', function () {
    $newObject = getLmdbDbaObject();
    $newObject->path = '/var/www/storagexxx';
    expect($newObject->createPathIfNone())->toBeTrue();
    shell_exec('rm -rf /var/www/storagexxx');
});
test('createPathIfNone() - when a file is present at the given path, it will delete and recreate the folder for that path - returns TRUE', function () {
    shell_exec('touch /var/www/storage_file');
    $newObject = getLmdbDbaObject();
    $newObject->path = '/var/www/storage_file';
    expect($newObject->createPathIfNone())->toBeTrue();
    shell_exec('rm -rf /var/www/storage_file');
});

/**
 * Testing firstKey()
 */
test('firstKey() - retrieves the first key on an existing store - should return TRUE', function () {
    shell_exec('rm -rf /var/www/storage');
    $this->dbaObject->open();
    $this->dbaObject->insert('111', 'hello world, test 1 2 3');
    $this->dbaObject->insert('222', 'hello world, test 1 3 3 - pass 02');

    expect($this->dbaObject->firstKey())->toEqual(111);
    //$this->dbaObject->fetch($this->dbaObject->firstKey())
});
test('firstKey() - retrieving a key on an EMPTY store - should return FALSE', function () {
    shell_exec('rm -rf /var/www/storage');
    $this->dbaObject->open();
    expect($this->dbaObject->firstKey())->toBeFalse();
});

/**
 * Testing nextKey()
 */
test('nextKey() - retrieving the first key on an EMPTY store - should return FALSE', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->nextKey())->toBeFalse();
});
test('nextKey() - retrieving the next key on an existing store - should return TRUE', function () {
    $this->dbaObject->open();
    $this->dbaObject->insert('147', 'hello world, this is 7PHP');
    $this->dbaObject->insert('247', 'hello world, this is 7PHP - pass 02');
    $who_cares_about_the_result = $this->dbaObject->firstKey();
    expect($this->dbaObject->nextKey())->toEqual(247);
});

/**
 * Testing replace()
 */
test('replace() - if successful, replacing value "hello world" for Key "44" by value "hello Mauritius" should return TRUE', function () {
    $this->dbaObject->open();
    $this->dbaObject->insert('44', 'hello world');
    expect($this->dbaObject->replace('44', 'hello Mauritius'))->toBeTrue();
});
it('confirms the updated value "hello Mauritius" is indeed present', function () {
    $this->dbaObject->open();
    expect($this->dbaObject->replace('44', 'hello Mauritius'))->toBeTrue();
    expect($this->dbaObject->fetch('44'))->toEqual('hello Mauritius');
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
 * Tear down
 */
afterAll(function () {
    if (file_exists(getLmdbDbaObject()->full_path)) {
        rmdirRecursively(getLmdbDbaObject()->path);
    }
});
