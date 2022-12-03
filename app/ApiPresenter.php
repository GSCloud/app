<?php
/**
 * GSC Tesseract
 * php version 7.4.0
 *
 * @category Framework
 * @package  Tesseract
 * @author   Fred Brooker <git@gscloud.cz>
 * @license  MIT https://gscloud.cz/LICENSE
 * @link     https://app.gscloud.cz
 */

namespace GSC;

use Cake\Cache\Cache;
use Nette\Neon\Neon;
use RedisClient\RedisClient;

/**
 * API Presenter
 * 
 * @category Framework
 * @package  Tesseract
 * @author   Fred Brooker <git@gscloud.cz>
 * @license  MIT https://gscloud.cz/LICENSE
 * @link     https://app.gscloud.cz
 */

class ApiPresenter extends APresenter
{
    const ACCESS_TIME_LIMIT = 3599;
    const API_CACHE = 'tenminutes';
    const MAX_API_HITS = 1000;
    const MAX_RECORDS = 100;
    const USE_CACHE = false;

    /**
     * Main controller
     * 
     * @return self
     */
    public function process()
    {
        \setlocale(LC_ALL, 'cs_CZ.UTF-8');
        \error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

        $cfg = $this->getCfg();
        $d = $this->getData();
        $match = $this->getMatch();
        $view = $this->getView();

        // view properties
        $presenter = $this->getPresenter();
        $use_key = $presenter[$view]["use_key"] ?? false;
        $allow_key = $presenter[$view]["allow_key"] ?? false;
        $priv = $presenter[$view]["private"] ?? false;

        // user data, permissions and authorizations
        $api_key = $_GET["apikey"] ?? null;
        $d["user"] = $this->getCurrentUser() ?? [];
        $user_id = $d["user"]["id"] ?? null;
        $d["admin"] = $user_group = $this->getUserGroup();
        if ($user_group) {
            $d["admin_group_${user_group}"] = true;
        }

        // general API properties
        $extras = [
            "fn" => $view,
            "name" => "REST API",
            "api_quota" => (int) self::MAX_API_HITS,
            "api_usage" => $this->accessLimiter(),
            "access_time_limit" => self::ACCESS_TIME_LIMIT,
            "cache_time_limit" => $this->getData("cache_profiles")[self::API_CACHE],
            "cached" => false,
            "records_quota" => self::MAX_RECORDS,
            "private" => $priv,
            "allow_key" => $allow_key,
            "use_key" => $use_key,
            "uuid" => $this->getUID(),
        ];

        // PRIVATE & NOT OAUTH2
        if ($priv && !$user_id) {
            return $this->writeJsonData(401, $extras);
        }

        // PRIVATE && OAUTH2 && NOT ALLOWED
        if ($priv && $user_id && !$user_group) {
            return $this->writeJsonData(401, $extras);
        }

        // process API calls
        switch ($view) {

        case "GetVersion":
            $data = [
                "version" => $this->getData('VERSION'),
            ];
            return $this->writeJsonData($data, $extras);
            break;

        default:
            sleep(5);
            return ErrorPresenter::getInstance()->process(404);
        }
        return $this;
    }

    /**
     * Redis access limiter
     *
     * @return mixed access count or null
     */
    public function accessLimiter()
    {
        $hour = date("H");
        $uid = $this->getUID();
        $key = "access_limiter_" . SERVER . "_" . PROJECT . "_${hour}_${uid}";
        \error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        $redis = new RedisClient(
            [
            'server' => 'localhost:6377',
            'timeout' => 1,
            ]
        );
        try {
            $val = (int) @$redis->get($key);
        } catch (\Exception $e) {
            return null;
        }
        if ($val > self::MAX_API_HITS) {
            // over limit!
            $this->setLocation("/err/420");
        }
        try {
            @$redis->multi();
            @$redis->incr($key);
            @$redis->expire($key, self::ACCESS_TIME_LIMIT);
            @$redis->exec();
        } catch (\Exception $e) {
            return null;
        }
        $val++;
        return $val;
    }
}
