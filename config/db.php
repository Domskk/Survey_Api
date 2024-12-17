<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PATCH");
header("Access-Control-Allow-Max-Age: 3600");
header("Access-Control-Allow-Header: Content-Type, Access-Control-Allow-Origin");
date_default_timezone_set("Asia/Manila");

define("SERVER", "localhost");
define("DB", "survey_db");
define("USER", "root");
define("PWORD", "");
define("TOKEN_KEY","SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c");
define("SECRET_KEY", "My_secret_key");


class Connection
{
    protected $connectionString = "mysql:host=" . SERVER . ";dbname=" . DB . ";charset=utf8";
    protected $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public function connect()
    {
        return new \PDO($this->connectionString, USER, PWORD, $this->options);
    }
}