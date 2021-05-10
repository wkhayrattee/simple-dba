<?php
/**
 * Simple dba - a simple wrapper around the php dba functions
 *
 * The actual implementation of our interface
 *
 * @author Wasseem Khayrattee <hey@wk.contact>
 */

namespace SimpleDba;

class Dba implements DbaInterface
{
    public string $handler_name;
    public string $full_path;
    public string $path;
    public string $store_name;
    public string $mode;
    /** @var resource */
    public $resource_handle = null;

    /**
     * You can find the list of handlers here: https://www.php.net/manual/en/dba.requirements.php
     *
     * @param string $path (without trailing slash)
     * @param string $store_name
     * @param string $handler_name
     */
    public function __construct(string $path, string $store_name, string $handler_name = 'lmdb')
    {
        $this->path = $path;
        $this->store_name = $store_name;
        $this->full_path = $path . DIRECTORY_SEPARATOR . $store_name;
        $this->handler_name = $handler_name;
    }

    /**
     * Open the dba
     *
     * @param string $mode
     *
     * @throws \Exception
     *
     * @return false|mixed|resource
     */
    public function open(string $mode = 'n')
    {
        $this->createPathIfNone();
        $this->mode = $mode;

        return $this->resource_handle = dba_open($this->full_path, $this->mode, $this->handler_name);
    }

    /**
     * Check if $path is valid
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function createPathIfNone()
    {
        if (is_dir($this->path)) {
            return true;
        } else {
            if (is_file($this->path)) {
                unlink($this->path);
            }
        }
        return mkdir($this->path, 0755, true);
    }

    /**
     * Open the dba persistently
     *
     * @param string $mode
     *
     * @throws \Exception
     *
     * @return false|mixed|resource
     */
    public function open_persistent(string $mode = 'n')
    {
        $this->createPathIfNone();
        $this->mode = $mode;

        return $this->resource_handle = dba_popen($this->full_path, $this->mode, $this->handler_name);
    }

    /**
     * Close a DBA database
     */
    public function close(): void
    {
        dba_close($this->resource_handle);
        $this->resource_handle = null; //else pestPHP issue: Failed asserting that NULL is null
        //see my github issue here: https://github.com/pestphp/pest/issues/294
    }

    /**
     * Delete DBA entry specified by key
     *
     * Note: we are also correcting an inconsistency here
     *
     * @param mixed $key
     */
    public function delete($key): bool
    {
        try {
            $result = dba_delete($key, $this->resource_handle);
            $this->optimise();
        } catch (\Exception $exception) {
            //that sucker returns an exception with message "MDB_NOTFOUND: No matching key/data pair found"
            //On the php docs, it says it will return FALSE - what a lie.
            //Let's make this consistent
            $result = false;
        }
        return $result;
    }

    /**
     * Check whether key exists
     *
     * @param bool $key
     */
    public function exists($key): bool
    {
        return dba_exists($key, $this->resource_handle);
    }

    /**
     * Fetch single data specified by key
     *
     * @param mixed $key
     */
    public function fetch($key)
    {
        return dba_fetch($key, $this->resource_handle);
    }

    /**
     * Fetch first key
     */
    public function firstKey()
    {
        return dba_firstkey($this->resource_handle);
    }

    /**
     * Fetch next key
     */
    public function nextKey()
    {
        return dba_nextkey($this->resource_handle);
    }

    /**
     * Insert entry
     *
     * @param $key
     * @param string $value
     *
     * @return bool
     */
    public function insert($key, string $value): bool
    {
        $result = dba_insert($key, $value, $this->resource_handle);
        $this->optimise();

        return $result;
    }

    /**
     * Optimizes the underlying database
     *
     * @codeCoverageIgnore
     */
    public function optimise(): bool
    {
        return dba_optimize($this->resource_handle);
    }

    /**
     * Synchronize database
     *
     * @codeCoverageIgnore
     */
    public function sync(): bool
    {
        return dba_sync($this->resource_handle);
    }

    /**
     * Replaces an entry
     *
     * @param string $key
     * @param string $value
     * @param resource $handler
     *
     * @return bool
     */
    public function replace(string $key, string $value, $handler): bool
    {
        //1) remove the existing entrey with given ley
        if ($this->exists($key)) {
            $this->delete($key);
        }
        //2) then, we re-add (i.e INSERT) that same key again, but with the updated value
        return $this->insert($key, $value);
    }
}
