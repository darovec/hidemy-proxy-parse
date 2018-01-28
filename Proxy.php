<?php

namespace darovec\hidemyproxy;

use Yii;
use yii\httpclient\Client;

class Proxy
{
    private $url = 'https://hidemy.name/ru/proxy-list/#list';

    /**
     * IP list
     * @var array
     */
    private $list = [];

    /**
     * active ip and port
     * @var array|null
     */
    private $activeIp = null;

    /**
     * instance of Proxy
     * @var $instance Proxy
     **/
    private static $instance = null;

    /**
     * error's codes
     */
    const ERROR_CODE_SOMEERROR = 10000;
    const ERROR_CODE_DOWNLOAD = 10001;
    const ERROR_CODE_PARSE = 10002;
    const ERROR_CODE_WRONG_ERROR_CODE = 10003;

    /**
     * list of error messages
     * @var $errorMessages array
     */
    private $errorMessages = [
        self::ERROR_CODE_SOMEERROR => 'some error',
        self::ERROR_CODE_DOWNLOAD => 'error with download page',
        self::ERROR_CODE_PARSE => 'error with parsing or empty list',
        self::ERROR_CODE_WRONG_ERROR_CODE => 'wong error code',
    ];

    /**
     * last error messages
     * @var $lastError array
     */
    private $lastError = [];

    /**
     * disable Proxy constructor.
     */
    private function __construct()
    {
        //
    }

    /**
     * disable clone
     */
    protected function __clone()
    {
        //
    }

    /**
     * @return Proxy
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * get first ip
     * @return string|null
     */
    public static function one()
    {
        if (!self::getInstance()->list) {
            self::getInstance()->parse();
        }

        if (self::getInstance()->list) {
            return current(self::getInstance()->list);
        } else {
            self::getInstance()->setError(self::ERROR_CODE_PARSE);
            return null;
        }
    }

    /**
     * get first page of ip
     * @return array
     */
    public static function all()
    {
        if (!self::getInstance()->list) {
            self::getInstance()->parse();
        }

        return self::getInstance()->list;
    }

    /**
     * get page fore parse
     * @return bool|string
     */
    private function getPage()
    {
        $cacheKey = 'darovec.hidemyproxy.getpage';

        $content = Yii::$app->cache->get($cacheKey);
        if ($content) {
            return $content;
        }

        $client = new Client(['baseUrl' => $this->url]);
        $request = $client->createRequest()
            ->setHeaders(['content-type' => 'text/html'])
            ->addHeaders(
                [
                    'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 
                    (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36'
                ]
            );

        $response = $request->send();

        if ($response->isOk) {
            $content = $response->getContent();
            Yii::$app->cache->set($cacheKey, $content, 120);

            return $content;
        } else {
            return false;
        }
    }

    /**
     * parse page
     */
    private function parse()
    {
        $this->setError(false);

        $content = $this->getPage();

        if ($content) {
            $pattern = "/<td class=tdl>(.*)<\/td><td>(.*)<\/td>/U";
            preg_match_all($pattern, $content, $matches);

            if (isset($matches[1][0]) && isset($matches[2][0])) {
                $this->list = [];
                foreach ($matches[1] as $cnt => $ip) {
                    if (isset($matches[2][$cnt])) {
                        $this->list[] = [
                            'ip' => $ip,
                            'port' => $matches[2][$cnt]
                        ];
                    }
                }
            } else {
                $this->setError(self::ERROR_CODE_PARSE);
            }
        } else {
            $this->setError(self::ERROR_CODE_DOWNLOAD);
        }
    }

    /**
     * set last error
     * @param integer|false $code
     * @param string|null $extMessage
     */
    private function setError($code, $extMessage = null)
    {
        if ($code === false) {
            $this->lastError = [];
        } else {
            if (!isset($this->errorMessages[$code])) {
                $code = self::ERROR_CODE_WRONG_ERROR_CODE;
            }

            $this->lastError = [
                'code' => $code,
                'message' => $this->errorMessages[$code]
            ];

            if ($extMessage) {
                $this->lastError['ext_message'] = $extMessage;
            }
        }
    }

    /**
     * return last error
     * @return array
     */
    public static function getLastError()
    {
        return self::getInstance()->lastError;
    }

    /**
     * set last active ip
     * @param $ip
     */
    public static function setActive($ip)
    {
        self::getInstance()->activeIp = $ip;
    }

    /**
     * get last active ip
     * @return array|null
     */
    public static function getActive()
    {
        return self::getInstance()->activeIp;
    }
}
