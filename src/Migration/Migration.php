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
    public function runAll()
    {
        $files = scandir($this->directory);
        if ($files === false) {
            return false;
        }
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $exploded = explode(' ', pathinfo($file, PATHINFO_FILENAME));
                if (!$this->run($exploded[0])) {
                    return false;
                }
            }
        }

        return true;
    }

    private static $index = [];

    /**
     * @param string $filestamp
     * @return bool
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    public function run(string $filestamp = '') : bool
    {
        if (str_contains($filestamp, $this->directory)) {
            $filestamp = str_replace($this->directory, '', $filestamp);
        }

        if (empty($filestamp)) {
            $this->buildIndex();
        }

        if (!array_key_exists($filestamp, self::$index)) {
            return false;
        }

        if (empty(self::$index[$filestamp])) {
            return false;
        }

        $file = self::$index[$filestamp];

        if (!file_exists($this->directory . '/'. $file . '.sql')) {
            return false;
        }

        $contents = file_get_contents($this->directory . $file . '.sql');
        if ($contents === false) {
            return false;
        }

        $sql = explode(';', $contents);
        foreach ($sql as $query) {
            $query = trim($query);
            $query = str_replace('{_DB_PREFIX}', $this->prefix, $query);
            if (!empty($query)) {
                if (!Db::getInstance()->execute($query)) {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @return void
     * @author George van Engers <george@dewebsmid.nl>
     * @since 11-04-2025
     */
    private function buildIndex() : void
    {
        $files = scandir($this->directory);
        if ($files !== false) {
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $exploded = explode(' ', pathinfo($file, PATHINFO_FILENAME));
                    self::$index[$exploded[0]] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }
    }
}