<?php

namespace App\Services\Connections;

use App\Exceptions\Connection;
use App\Interfaces\Host;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Scp
{
    use SerializesModels;

    const download = "/var/www/storage/";

    protected string $pathDownload;


    public function __construct
    (
        protected Host $server,
        protected string $to
    )
    {}

    public function setPathDownload(string $path)
    {
        $this->pathDownload = $path;
        return $this;
    }

    private function makeShhPass(): string
    {
        return "sshpass -p '".$this->server->getPass()."'";
    }
    /**
     * @param string $from куда загружать файлы
     * @return string
     */
    private function makeScp(): string
    {
        return "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -rq ".
            $this->server->getLogin().
            "@".$this->server->getHost().
            ":".$this->pathDownload.
            " ".self::download.$this->to;
    }

    public function download()
    {
        $exec = $this->makeShhPass() ." ".$this->makeScp();
        $output = [];
        $code = 0;
        exec($exec, $output, $code);
        Log::info(json_encode($output, JSON_PRETTY_PRINT));
        Log::info($code);
        Log::info($exec);
        if ($code != 0) {
            throw new Connection(json_encode($output, JSON_PRETTY_PRINT), 404);
        }
    }
}
