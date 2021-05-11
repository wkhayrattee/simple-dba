<?php
/**
 * SetUp
 */
beforeEach(function () {
    /** @var \SimpleDba\Dba */
    $this->flatFileObject = getFlatFileDbaObject();
});

/**
 * Testing Constructor
 */
test('Constructor has set path to: "' . getFlatFilePath() . '"', function () {
    expect($this->flatFileObject->path)->toBe(getFlatFilePath());
});

test('Constructor has set store_name to: "' . getFlatFileStoreName() . '"', function () {
    expect($this->flatFileObject->store_name)->toBe(getFlatFileStoreName());
});

test('Constructor has set handle_name to: "' . getHandlerName('flatfile') . '"', function () {
    expect($this->flatFileObject->handler_name)->toBe(getHandlerName('flatfile'));
});

it('has also set the full path to: "' . getFlatFilePath() . DIRECTORY_SEPARATOR . getFlatFileStoreName() . '"', function () {
    expect($this->flatFileObject->full_path)->toBe(getFlatFilePath() . DIRECTORY_SEPARATOR . getFlatFileStoreName());
});

/**
 * Testing Open() and Open Persistent
 */
test('open() - returned a resource handle to an opened connection to dba', function () {
    expect($this->flatFileObject->open('c'))->toBeResource();
    $this->flatFileObject->close();
});

//test('open_persistent() - returned a resource handle to a persistent opened connection to dba', function () {
//    expect($this->flatFileObject->open_persistent())->toBeResource();
//    $this->flatFileObject->close();
//});

/**
 * Testing fetch()
 */
test('fetch() - retrieval of a value "hello world" for the key "123" should be successful', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->insert('123', 'hello world');
    expect($this->flatFileObject->fetch('123'))->toEqual('hello world');
    $this->flatFileObject->close();
});
test('fetch() - retrieval of an unknown key "123x" should return false', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->fetch('123x'))->toBeFalse();
    $this->flatFileObject->close();
});

/**
 * Testing insert()
 */
test('insert() - Insertion of a key "any_key_name", with value "any value" - should return TRUE', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->insert('any_key_name', 'any value');
    expect($this->flatFileObject->exists('any_key_name'))->toBeTrue();
    $this->flatFileObject->close();
});
it('seems to have been inserted, as the key "any_key_name" is present', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->exists('any_key_name'))->toBeTrue();
    $this->flatFileObject->close();
});
it('was indeed well inserted, as the key "any_key_name" has the intended value "any value"', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->fetch('any_key_name'))->toEqual('any value');
    $this->flatFileObject->close();
});
test('insert() - Another insertion of the existing key "any_key_name", with another value "an updated value" - should fail, returning FALSE', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->insert('any_key_name', 'an updated value'))->toBeFalse();
    $this->flatFileObject->close();
});
it('confirms the original inserted value is still "any value"', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->fetch('any_key_name'))->toEqual('any value');
    $this->flatFileObject->close();
});

/**
 * Testing exists()
 */
test('exists() - Checking for existence of the stored key "7" - should return TRUE', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->insert('7', 'hello world, this is 7PHP');
    expect($this->flatFileObject->exists('7'))->toBeTrue();
    $this->flatFileObject->close();
});

test('exists() - Checking for existence of the unknown key "777" - should return FALSE', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->exists('777'))->toBeFalse();
    $this->flatFileObject->close();
});

/**
 * Testing delete()
 */
test('delete() - Inserting key "2021" and deleting it - successful deletion should return TRUE', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->insert('2021', 'hello world, this is 7PHP 2021');
    expect($this->flatFileObject->delete('2021'))->toBeTrue();
    $this->flatFileObject->close();
});
it('also allows for Insertion of a key string "this_is_a_key" and deleting it - successful deletion should return TRUE', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->insert('this_is_a_key', 'hello world, this is 7PHP');
    expect($this->flatFileObject->delete('this_is_a_key'))->toBeTrue();
    $this->flatFileObject->close();
});
test('delete() - Deleting an unknown key "this_is_not_a_saved_key" - should return FALSE', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->delete('this_is_not_a_saved_key'))->toBeFalse();
    $this->flatFileObject->close();
});

/**
 * Testing createPathIfNone()
 */
test('createPathIfNone() - checking if the existing path "/var/www/storage" is there - returns TRUE', function () {
    $newObject = getFlatFileDbaObject();
    $newObject->path = '/var/www/storage';
    expect($newObject->createPathIfNone())->toBeTrue();
});
test('createPathIfNone() - will attempt to create unknown path "/var/www/storagexxx" and on success returns TRUE', function () {
    $newObject = getFlatFileDbaObject();
    $newObject->path = '/var/www/storagexxx';
    expect($newObject->createPathIfNone())->toBeTrue();
    shell_exec('rm -rf /var/www/storagexxx');
});
test('createPathIfNone() - when a file is present at the given path, it will delete and recreate the folder for that path - returns TRUE', function () {
    shell_exec('touch /var/www/storage_file');
    $newObject = getFlatFileDbaObject();
    $newObject->path = '/var/www/storage_file';
    expect($newObject->createPathIfNone())->toBeTrue();
    shell_exec('rm -rf /var/www/storage_file');
});

/**
 * Testing firstKey()
 */
test('firstKey() - retrieves the first key on an existing store - should return TRUE', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->firstKey())->toEqual('123');
    $this->flatFileObject->close();
});
test('firstKey() - retrieving a key on an EMPTY store - should return FALSE', function () {
    shell_exec('rm -rf /var/www/storage');
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->firstKey())->toBeFalse();
    $this->flatFileObject->close();
});

/**
 * Testing nextKey()
 */
test('nextKey() - retrieving the first key on an EMPTY store - should return FALSE', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->nextKey())->toBeFalse();
    $this->flatFileObject->close();
});
test('nextKey() - retrieving the next key on an existing store - should return TRUE', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->insert('1', 'hello world, this is 7PHP');
    $this->flatFileObject->insert('2', 'hello world, this is 7PHP - pass 02');
    $who_cares_about_the_result = $this->flatFileObject->firstKey();
    expect($this->flatFileObject->nextKey())->toEqual(2);
    $this->flatFileObject->close();
});

/**
 * Testing replace()
 */
test('replace() - if successful, replacing value "hello world" for Key "44" by value "hello Mauritius" should return TRUE', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->insert('44', 'hello world');
    expect($this->flatFileObject->replace('44', 'hello Mauritius'))->toBeTrue();
    $this->flatFileObject->close();
});
it('confirms the updated value "hello Mauritius" is indeed present', function () {
    $this->flatFileObject->open('c');
    expect($this->flatFileObject->replace('44', 'hello Mauritius'))->toBeTrue();
    expect($this->flatFileObject->fetch('44'))->toEqual('hello Mauritius');
    $this->flatFileObject->close();
});

/**
 * Testing Close()
 */
test('Close() - When Closing the connection, the handler is indeed null', function () {
    $this->flatFileObject->open('c');
    $this->flatFileObject->close();
    expect($this->flatFileObject->resource_handle)->toBeNull();
});

/**
 * Tear down
 */
afterAll(function () {
    if (file_exists(getFlatFileDbaObject()->full_path)) {
        rmdirRecursively(getFlatFileDbaObject()->path);
    }
});
