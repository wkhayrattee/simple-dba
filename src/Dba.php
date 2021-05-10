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
    public $handler_name;
    public $path;
    public $mode;
    /** @var resource */
    public $resource_handle;

    /**
     * You can find the list of handlers here: https://www.php.net/manual/en/dba.requirements.php
     *
     * @param string $path
     * @param string $handler
     * @param string $handler_name
     */
    public function __construct(string $path, string $handler_name = 'lmdb')
    {
        $this->path = $path;
        $this->handler_name = $handler_name;
    }

    /**
     * Open the dba
     *
     * @param string $mode
     *
     * @return mixed|void
     */
    public function open(string $mode = 'n')
    {
        $this->mode = $mode;
        return $this->resource_handle = dba_open($this->path, $this->mode, $this->handler_name);
    }

    /**
     * Open the dba persistently
     *
     * @param string $mode
     *
     * @return mixed|void
     */
    public function open_persistent(string $mode = 'n')
    {
        $this->mode = $mode;
        return $this->resource_handle = dba_popen($this->path, $this->mode, $this->handler_name);
    }

    /**
     * Close a DBA database
     */
    public function close(): void
    {
        dba_close($this->resource_handle);
        $this->resource_handle = null; //else pestPHP issue: Failed asserting that NULL is null
    }

    /**
     * Delete DBA entry specified by key
     *
     * @param mixed $key
     */
    public function delete($key): bool
    {
        $result = dba_delete($key, $this->resource_handle);
        $this->optimise();

        return $result;
    }

    /**
     * Check whether key exists
     *
     * @param mixed $key
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
     * @codeCoverageIgnore
     */
    public function optimise(): bool
    {
        return dba_optimize($this->resource_handle);
    }

    /**
     * Synchronize database
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
