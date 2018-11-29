<?php

namespace App\Library\Ssh2;

class Client
{
    protected $authenticated = false;

    protected $host;

    protected $port;

    protected $username;

    protected $publicKey;

    protected $privateKey;

    protected $connection = null;

    public function __construct($host, $port, $username, $publicKey, $privateKey)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function connect()
    {
        if ($this->connection) {
            return true;
        }

        if (!($this->connection = ssh2_connect($this->host, $this->port))) {
            throw new Exception(sprintf('ssh2 connect %s:%d failed', $this->host, $this->port));
        }
    }

    public function authenticate()
    {
        if ($this->authenticated) {
            return true;
        }

        $this->connect();

        if (!($this->authenticated = @ssh2_auth_pubkey_file($this->connection, $this->username, $this->publicKey, $this->privateKey))) {
            $this->disconnect();
            throw new Exception(sprintf('ssh2 authenticate failed: %s %s %s', $this->username, $this->publicKey, $this->privateKey));
        }
    }

    protected function exec($command)
    {
        $this->authenticate();

        if (!($stdoutStream = @ssh2_exec($this->connection, $command))) {
            // $this->disconnect();
            throw new Exception(sprintf('Execute %s on %s@%s', $command, $this->username, $this->host));
        }

        if (!($stderrStream = @ssh2_fetch_stream($stdoutStream, SSH2_STREAM_STDERR))) {
            throw new Exception('Fetch stderr stream failed');
        }

        stream_set_blocking($stdoutStream, true);
        stream_set_blocking($stderrStream, true);
        
        return [$stdoutStream, $stderrStream];
    }

    public function run($command)
    {
        list($stdout, $stderr) = $this->exec($command);

        $error = stream_get_contents($stderr);
        if (!empty($error)) {
            fclose($stdout);
            fclose($stderr);
            return trim($error);
        }

        $output = stream_get_contents($stdout);
        fclose($stdout);
        fclose($stderr);
        return trim($output);
    }

    public function download($remote, $local)
    {
        $this->authenticate();

        if (!@ssh2_scp_recv($this->connection, $remote, $local)) {
            throw new Execption(sprintf('Download %s@%s:%s => %s failed', $this->username, $this->host, $remote, $local));
        }
    }

    public function upload($local, $remote, $createMode = 0644)
    {
        $this->authenticate();

        if (!@ssh2_scp_send($this->connection, $remote, $local)) {
            throw new Execption(sprintf('Upload %s@%s:%s => %s failed', $this->username, $this->host, $remote, $local));
        }
    }

    public function cat($file)
    {
        return $this->run(sprintf('cat %s', $file));
    }

    public function tail($file, $lineNumber)
    {
        return $this->run(sprintf('tail -n %d %s', $lineNumber, $file));
    }

    public function tailToLocal($remote, $lineNumber, $local)
    {
        $tmp = sprintf('/tmp/backyard.%d', mt_rand(10000, 99999));
        $command = sprintf('tail -n %d %s > %s', $lineNumber, $remote, $tmp);
        $result = $this->run($command);

        if (!empty($result)) {
            throw new Exception(sprintf('TailToLocal %s => %s failed: %s', $remote, $local, $result));
        }

        try {
            $this->download($tmp, $local);
        } catch(Exception $ex) {
            throw $ex;
        } finally {
            $this->run(sprintf('rm -f %s', $tmp));
        }
    }

    public function inode($file)
    {
        return $this->run(sprintf('stat -c %%i %s', $file));
    }

    public function lineNumber($file)
    {
        $command = sprintf('wc -l %s | awk \'{print $1}\'', $file);
        return (int) $this->run($command);
    }

    public function test($conditions)
    {
        $command = "if {$conditions}; then echo true; fi";
        return $this->run($command) === 'true';
    }

    public function disconnect()
    {
        return $this->connection && @ssh2_disconnect($this->connection);
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}