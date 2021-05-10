<?php
/**
 * Simple dba - a simple wrapper around the php dba functions
 *
 * While using the default dba functions provided by PHP, I have seen some inconsistencies and also wherever applicable, I would like to change certain things, specially the dba_replace..etc
 *
 * @author Wasseem Khayrattee <hey@wk.contact>
 */

namespace SimpleDba;

interface DbaInterface
{
    /**
     * Should create a resource handler with the given path
     * Will call open()
     *
     * DbaInterface constructor.
     *
     * @param string $path (absolute path to the folder where your store will live)
     * @param string $store_name (the name of your store)
     * @param string $handler_name
     */
    public function __construct(string $path, string $store_name, string $handler_name);

    /**
     * Close a DBA database
     *
     * closes the established database and frees all resources of the specified database handle
     */
    public function close(): void;

    /**
     * Delete DBA entry specified by key
     * We will also perform an optimise
     *
     * Note that the memory is lost after doing this.
     * If you had a key 'foo' with a value of 1000 bytes, that 1000 bytes is lost,
     * and the DB file size will reflect this.
     *
     * @param $key
     *
     * @return bool (Returns true on success or false on failure)
     */
    public function delete($key): bool;

    /**
     * Check whether key exists
     *
     * @param $key
     *
     * @return bool (true if the key exists, false otherwise)
     */
    public function exists($key): bool;

    /**
     * Fetch single data specified by key
     *
     * @param $key
     *
     * @return mixed (Returns the associated string if the key/data pair is found, false otherwise)
     */
    public function fetch($key);

    /**
     * Fetch first key
     *
     * returns the first key of the database and resets the internal key pointer.
     * This permits a linear search through the whole database
     *
     * @return mixed (Returns the key on success or false on failure)
     */
    public function firstKey();

    /**
     * Fetch the next key of the database and advances the internal key pointer
     *
     * @return mixed (Returns the key on success or false on failure)
     */
    public function nextKey();

    /**
     * Inserts the entry described with key and value into the database
     *
     * @param $key
     * @param string $value
     *
     * @return bool (true on success or false on failure)
     */
    public function insert($key, string $value): bool;

    /**
     * Open the dba
     * establishes a database instance for path with mode using handler
     *
     * use 'n' for create, truncate and read/write access
     *
     * @param string $mode (see: https://www.php.net/manual/en/function.dba-open.php)
     *
     * @return mixed (Returns a positive (resource) handle on success or false on failure)
     */
    public function open(string $mode = 'n');

    /**
     * Open the dba persistently
     * establishes a database instance for path with mode using handler
     *
     * use 'n' for create, truncate and read/write access
     *
     * @param string $mode (see: https://www.php.net/manual/en/function.dba-open.php)
     *
     * @return mixed (Returns a positive (resource) handle on success or false on failure)
     */
    public function open_persistent(string $mode = 'n');

    /**
     * Optimizes the underlying database
     *
     * Use "dba_optimize" to optimize a database, which usually consists of
     * eliminating gaps between records created by deletes
     *
     * NOTE: Should probably be called after each insert/delete
     * Because whe you add-remove-substitute keys with data having different content length,
     * the db continues to grow, wasting space. So, it is necessary, sometimes, to re-pack the
     * db in order to remove unused data from the db itself
     *
     * @return bool (true on success or false on failure)
     */
    public function optimise(): bool;

    /**
     * Synchronizes the database. This will probably trigger a physical write to the disk, if supported
     *
     * Synchronizes the view of the database in memory and its image on the disk.
     * As you insert records, they may be cached in memory by the underlying engine.
     * Other processes reading from the database will not see these new records until synchronization
     *
     * @return bool (true on success or false on failure)
     */
    public function sync(): bool;

    /**
     * Replaces an entry
     *
     * NOTE: We will actually delete the previous entry by key
     * and then insert that same key with the new value.
     *
     * This is because some inconsistencies was reported with the traditiona dba_replace
     * function, see here in user contributed notes: https://www.php.net/manual/en/function.dba-replace.php
     *
     * @param string $key
     * @param string $value
     * @param resource $handler
     *
     * @return bool (true on success or false on failure)
     */
    public function replace(string $key, string $value, $handler): bool;
}
