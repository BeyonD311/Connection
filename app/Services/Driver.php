<?php

namespace App\Services;

use App\Exceptions\Config;
use App\Interfaces\Host;

final class Driver
{
    private string $name;
    public function __construct(
        protected Host $db
    ){
    }

    /**
     * @param string $name
     * @param string $driver
     * @param string $database
     * @throws Config
     */
    public function setDriver(string $name, string $driver, string $database)
    {
        app('db');
        if(empty($this->newConfig = config('database')['connections'][$driver])) {
            throw new Config("Драйвер не установлен");
        }
        $this->name = "$name-{$this->db->getId()}";
        $newConfig = config("database.connections.mysql");
        $newConfig['driver'] = $driver;
        $newConfig['host'] = $this->db->getHost();
        $newConfig['port'] = $this->db->getPort();
        $newConfig['username'] = $this->db->getLogin();
        $newConfig['password'] = $this->db->getPass();
        $newConfig['database'] = $database;
        $newConfig['strict'] = false;
        config(['database.connections.'.$this->name => $newConfig]);
    }

    public function getConfig(): string
    {
        return $this->name;
    }

}
