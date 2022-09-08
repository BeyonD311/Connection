<?php

namespace App\Models;

use App\Services\Connections\Options\DB;
use App\Services\Connections\Options\Server;
use Illuminate\Database\Eloquent\Collection;

class Connections extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'connections';

    protected $fillable = [
        "id",
        "name",
        "mac_address",
        "type_connection"
    ];

    public function databaseConnection()
    {
        return $this->hasOne(DatabaseConnections::class, 'connection_id', 'id');
    }

    public function serverConnection()
    {
        return $this->hasOne(ServerConnections::class, 'connection_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(Files::class, 'connections_id', 'id');
    }

    public function getWorkingConnections(): Collection
    {
        return static::all();
    }

    public function getWorkingConnection(array $options): array
    {
        return $this->where([
            ['id', '=', $options['connection']]
        ])->get()
            ->map(function ($item) use ($options) {
                $items = $item->files()->where([
                    ["call_at", ">=", $options['date_from']],
                    ["call_at", "<=", $options['date_to']]
                ])->get()->map(function ($item) {
                    return [
                        "name" => $item->name,
                        "connection_id" => $item->{"connection_id"},
                        "exception" => $item->exception
                    ];
                });
                return [
                    "name" => $item->name,
                    "id" => $item->id,
                    "files" => $items->toArray()
                ];
            })->toArray()[0];
    }

    public function infoFromConnection(int $id)
    {
        $connection = self::with(['serverConnection', 'databaseConnection'])->where([
            ['id', '=', $id]
        ])->first();
        $db = $connection->getRelation('databaseConnection');
        $server = $connection->getRelation('serverConnection');
        if(isset($db)) {
            $db = (new DB())
                ->setPass($db->pass)
                ->setLogin($db->login)
                ->setConnectionId($id)
                ->setPort($db->port)
                ->setId($db->id)
                ->setHost($db->host);
        }
        if(isset($server)) {
            $server = (new Server())
                ->setHost($server->host)
                ->setPort($server->port)
                ->setLogin($server->login)
                ->setConnectionId($id)
                ->setPass($server->pass)
                ->setId($server->id);
        }
        $connection = $connection->toArray();
        $connection['server_connection'] = $server;
        $connection['database_connection'] = $db;

        return $connection;
    }

    private function camelCase($name): string
    {
        $str = explode("_", $name);
        foreach ($str as &$value) {
            $value = ucfirst($value);
        }
        return implode("", $str);
    }
}
