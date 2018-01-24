<?php

namespace darovec\hidemyproxy;

class Parse
{
    private $url = 'https://hidemy.name/ru/proxy-list/?type=h#list';
    private $list = [];
    /**@var $instance Parse **/
    private $instance = null;

    public function __construct()
    {
        return self::init();
    }

    /**
     * @return Parse
     */
    public static function init()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function one()
    {

    }

    public static function all()
    {
        if (!self::$list) {
            self::init()->list = self::init()->parse();
        }

        return self::init()->list;
    }

    private function parse()
    {
        echo file_get_contents($this->url);die;
    }
}
