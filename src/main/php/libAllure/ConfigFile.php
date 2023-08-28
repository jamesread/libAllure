<?php

namespace libAllure;

class ConfigFile
{
    private array $keys;

    public $filename = '/config.ini';

    public function __construct(array $additionalKeys = null, $useDefaultKeys = true)
    {
        $this->keys = [];

        if ($useDefaultKeys) {
            $this->keys['DB_NAME'] = 'unknown';
            $this->keys['DB_HOST'] = 'localhost';
            $this->keys['DB_USER'] = 'user';
            $this->keys['DB_PASS'] = 'password';
            $this->keys['TIMEZONE'] = 'Europe/London';
        }

        if (is_array($additionalKeys)) {
            $this->keys = array_merge($this->keys, $additionalKeys);
        }
    }

    public function tryLoad(array $possiblePaths)
    {
        $foundAConfig = false;

        foreach ($possiblePaths as $path) {
            if (file_exists($path . $this->filename)) {
                $foundAConfig = true;
                $this->load($path . $this->filename);
            }
        }

        if (!$foundAConfig) {
            $this->createConfigFile($possiblePaths[0]);
        }
    }

    private function createConfigFile($path)
    {
        if (!is_writable($path)) {
            throw new \Exception('Could not save a default config file as the path is not writable:  ' . $path);
        }

        $content = '';

        foreach ($this->keys as $key => $val) {
            $content .= $key . '=' . $val . "\n";
        }

        file_put_contents($path . $this->filename, $content);
    }

    private function load($fullpath)
    {
        $this->keys = parse_ini_file($fullpath, false);
    }

    public function getAll(): array
    {
        return $this->keys;
    }

    public function get($k): ?string
    {
        if (isset($this->keys[$k])) {
            return $this->keys[$k];
        }

        return null;
    }

    public function getDsn($type = 'mysql'): string
    {
        return $type . ':dbname=' . $this->get('DB_NAME');
    }
}
