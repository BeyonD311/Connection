<?php

namespace App\Services\DataService;

use App\Services\Connections\Scp;
use App\Services\Driver;
use App\Services\Hosts\Host;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Asterisk extends DataService
{
    const path = "/var/spool/asterisk/monitor/";

    protected string $lastUpdateConnection = "database_connection_id";

    public function __construct(
        protected Host $server,
        protected Host $db
    )
    {
        parent::__construct();
    }

    public function getItems(): \Generator
    {
        $db = app('db');
        $driver = new Driver($this->db);
        $driver->setDriver('asterisk', 'mysql','asteriskcdrdb');
        $date = date('Y-m-d H:i:s', $this->getInstanceLastUpdate()->getTimestamp($this->db->getId()));
        $items = $db->connection($driver->getConfig())->table('cdr')
            ->where('calldate', '>', $date)
            ->orderBy('calldate', 'desc');
        foreach ($items->get()->toArray() as $item) {
            if($item->recordingfile == false) {
                continue;
            }
            yield $item;
        }
    }

    public function download()
    {
        $scp = new Scp($this->server, 'audio');
        $items = $this->getItems();
        if(!empty($items->current())) {
            $this->getInstanceLastUpdate()->updateOrCreate($this->db->getId(), $items->current()->calldate);
            foreach ($items as $item) {
                if($item->recordingfile != "") {
                    $path = self::path.date("Y/m/d", strtotime($item->calldate)). "/".$item->recordingfile;
                    $scp->setPathDownload($path);
                    Artisan::call('file', [
                        'connections' => serialize($scp),
                        'item' => $item,
                        'type' => "Asterisk"
                    ]);
                }
            }
        }
    }
}
