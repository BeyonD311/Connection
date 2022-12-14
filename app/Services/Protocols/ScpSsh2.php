<?php
declare(strict_types=1);

namespace App\Services\Protocols;

use App\Exceptions\Connection;

class ScpSsh2 extends Scp
{
    /**
     * @throws Connection
     */
    public function connect(): void
    {
        $this->connect = ssh2_connect($this->server->getHost(), $this->server->getPort(), null, callbacks: [
            "disconnect" => function($reason, $message, $language) {
                dump($reason, $message, $language);
            }
        ]);
        if($this->connect === false) {
            throw new Connection("Нет подключения к серверу");
        }
        if(!ssh2_auth_password($this->connect, $this->server->getLogin(), $this->server->getPass())) {
            throw new Connection("Аутентификация не пройдена");
        }
    }

    /**
     * @throws Connection
     */
    public function disconnect(): void
    {
        if(!ssh2_disconnect($this->connect)) {
            throw new Connection("Ошибка при закрытии соединения");
        }
    }

    /**
     * @return string
     * @throws Connection
     */
    public function download(): string
    {
        $this->connect();
        $pathDownload = $this->download.$this->to."/".$this->generateOutputName();
        if(!ssh2_scp_recv($this->connect, $this->pathDownload, $pathDownload)) {
            throw new Connection("При загрузке файла произошла ошибка");
        }
        $this->disconnect();
        return $pathDownload;
    }

    /**
     * @return string
     */
    protected function generateOutputName(): string
    {
        $sliceName = explode("/", $this->pathDownload);
        $name = array_pop($sliceName);
        $name = explode(".", $name);
        $extension = array_pop($name);
        $name = implode(".", $name)."-".$this->server->getConnectionId().".".$extension;
        unset($sliceName,$extension);
        return $name;
    }
}
