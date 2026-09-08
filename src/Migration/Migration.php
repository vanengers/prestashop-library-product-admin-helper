<?php

namespace Vanengers\PrestaShopLibrary\ProductAdminHelper\Migration;

use Db;

class Migration
{
    private static ?self $instance = null;
    /** @var string directory */
    private string $directory = '';

    /** @var string prefix */
    private string $prefix = '';

    /** @var array<string, string>|null filestamp => filename (without extension), null when not built yet */
    private ?array $index = null;

    public static function getInstance() : self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $directory
     * @return $this
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public function setDirectory(string $directory) : self
    {
        $directory = $directory === ''
            ? ''
            : rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;

        if ($directory !== $this->directory) {
            $this->index = null;
        }

        $this->directory = $directory;
        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public function setPrefix(string $prefix) : self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return bool
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public function runAll() : bool
    {
        if ($this->index === null && !$this->buildIndex()) {
            return false;
        }

        foreach (array_keys($this->index) as $filestamp) {
            if (!$this->run($filestamp)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $filestamp
     * @return bool
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public function run(string $filestamp = '') : bool
    {
        $filestamp = $this->normalizeFilestamp($filestamp);

        if ($this->index === null && !$this->buildIndex()) {
            return false;
        }

        if (empty($this->index[$filestamp])) {
            return false;
        }

        $path = $this->directory . $this->index[$filestamp] . '.sql';

        if (!is_file($path)) {
            return false;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return false;
        }

        // Replace the prefix once for the whole file instead of once per query.
        $contents = str_replace('{_DB_PREFIX_}', $this->prefix, $contents);

        $db = Db::getInstance();
        foreach (explode(';', $contents) as $query) {
            $query = trim($query);
            if ($query === '') {
                continue;
            }

            if (!$db->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Accepts a bare filestamp, a filename or a full path and reduces it to the
     * filestamp used as index key.
     *
     * @param string $filestamp
     * @return string
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    private function normalizeFilestamp(string $filestamp) : string
    {
        if ($this->directory !== '' && str_contains($filestamp, $this->directory)) {
            $filestamp = str_replace($this->directory, '', $filestamp);
        }

        if (str_contains($filestamp, '.')) {
            $filestamp = pathinfo($filestamp, PATHINFO_FILENAME);
        }

        return self::filestampOf($filestamp);
    }

    /**
     * @return bool true when the directory could be read
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    private function buildIndex() : bool
    {
        $this->index = [];

        if ($this->directory === '' || !is_dir($this->directory)) {
            return false;
        }

        $files = scandir($this->directory);
        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            // Cheaper than pathinfo() and skips '.' / '..' as well.
            if (substr($file, -4) !== '.sql') {
                continue;
            }

            $name = substr($file, 0, -4);
            $this->index[self::filestampOf($name)] = $name;
        }

        return true;
    }

    /**
     * @param string $name
     * @return string
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    private static function filestampOf(string $name) : string
    {
        $stamp = strstr($name, ' ', true);

        return $stamp === false ? $name : $stamp;
    }
}
