<?php

namespace Vanengers\PrestaShopLibrary\ProductAdminHelper\Migration;

use Db;

class Migration
{
    private static ?self $instance = null;
    /** @var string directory */
    private string $directory = '';

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
                if (!$this->run(pathinfo($file, PATHINFO_FILENAME))) {
                    return false;
                }
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
        if (str_contains($filestamp, $this->directory)) {
            $filestamp = str_replace($this->directory, '', $filestamp);
        }

        if (!file_exists($this->directory . $filestamp . '.sql')) {
            return false;
        }

        $contents = file_get_contents($this->directory . $filestamp . '.sql');
        if ($contents === false) {
            return false;
        }

        $sql = explode(';', $contents);
        foreach ($sql as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!Db::getInstance()->execute($query)) {
                    return false;
                }
            }
        }

        return false;
    }
}