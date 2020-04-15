<?php


namespace app\models\cbr;


/**
 * Trait CBRTrait
 * @package app\models\cbr
 *
 * @property integer $tomorrowTimestamp
 */
trait CBRTrait
{

    /**
     * UNIX timestamp на начало сегодняшнего дня
     *
     * @return false|int
     */
    public static function getTomorrowTimestamp()
    {
        return strtotime("tomorrow") + self::SECONDS_IN_DAY;
    }
}