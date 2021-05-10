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
    private $handler_name;
    private $path;
    public $mode;
    /** @var resource */
    private $resource_handle;

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
     * @param string $path
     * @param string $mode
     *
     * @return mixed|void
     */
    public function open(string $path, string $mode = 'n')
    {
        $this->mode = $mode;
        $this->resource_handle = dba_open($this->path, $this->mode, $this->handler_name);
    }

    /**
     * Open the dba persistently
     *
     * @param string $path
     * @param string $mode
     *
     * @return mixed|void
     */
    public function open_persistent(string $path, string $mode = 'n')
    {
        $this->mode = $mode;
        $this->resource_handle = dba_popen($this->path, $this->mode, $this->handler_name);
    }

    /**
     * Close a DBA database
     */
    public function close(): void
    {
        dba_close($this->resource_handle);
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
     * @param mixed $key
     * @param string $value
     */
    public function insert($key, string $value): bool
    {
        $result = dba_insert($key, $value, $this->resource_handle);
        $this->optimise();

        return $result;
    }

    /**
     * Optimizes the underlying database
     */
    public function optimise(): bool
    {
        return dba_optimize($this->resource_handle);
    }

    /**
     * Synchronize database
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
     * @param mixed $handler
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