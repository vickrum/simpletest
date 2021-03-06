<?php

class PageRequest
{
    private $parsed;

    public function __construct($raw)
    {
        $statements = explode('&', $raw);
        $this->parsed = [];
        foreach ($statements as $statement) {
            if (false === strpos($statement, '=')) {
                continue;
            }
            $this->parseStatement($statement);
        }
    }

    private function parseStatement($statement)
    {
        list($key, $value) = explode('=', $statement);
        $key = urldecode($key);
        if (preg_match('/(.*)\[\]$/', $key, $matches)) {
            $key = $matches[1];
            if (!isset($this->parsed[$key])) {
                $this->parsed[$key] = [];
            }
            $this->addValue($key, $value);
        } elseif (isset($this->parsed[$key])) {
            $this->addValue($key, $value);
        } else {
            $this->setValue($key, $value);
        }
    }

    private function addValue($key, $value)
    {
        if (!is_array($this->parsed[$key])) {
            $this->parsed[$key] = [$this->parsed[$key]];
        }
        $this->parsed[$key][] = urldecode($value);
    }

    private function setValue($key, $value)
    {
        $this->parsed[$key] = urldecode($value);
    }

    public function getAll()
    {
        return $this->parsed;
    }

    public static function get()
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            $request = new self($_SERVER['QUERY_STRING']);

            return $request->getAll();
        }

        return [];
    }

    public static function post()
    {
        $request = new self(file_get_contents('php://input'));

        return $request->getAll();
    }
}
