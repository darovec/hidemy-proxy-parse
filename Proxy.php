<?php

namespace darovec\hidemyproxy;

use yii\httpclient\Client;

class Proxy
{
    private $url = 'https://hidemy.name/ru/proxy-list/?ports=80&type=h#list';

    /**
     * IP list
     * @var array
     */
    private $list = [];

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
     * parse page
     */
    private function parse()
    {
        $this->setError(false);

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
            $pattern = "/<td class=tdl>(.*)<\/td>/U";
            preg_match_all($pattern, $content, $matches);

            if (isset($matches[1][0])) {
                $this->list = [];
                foreach ($matches[1] as $ip) {
                    $this->list[] = $ip;
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
     * @return array
     */
    public static function getLastError()
    {
        return self::getInstance()->lastError;
    }
}
