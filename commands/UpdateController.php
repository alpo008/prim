<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\cbr\CurrencyPeriod;
use app\models\Currency;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UpdateController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @return int Exit code
     */
    public function actionIndex()
    {
        if ($startTimestamp = Currency::getLastFilledDate()) {
            $startTimestamp += CurrencyPeriod::SECONDS_IN_DAY;
        }
        $stopTimestamp = CurrencyPeriod::getTomorrowTimestamp();
        if (!$startTimestamp || !$stopTimestamp) {
            $currencyPeriod = new CurrencyPeriod();
        } elseif ($stopTimestamp >= $startTimestamp) {
            $currencyPeriod = new CurrencyPeriod(compact('startTimestamp', 'stopTimestamp'));
        }
        if (isset($currencyPeriod)) {
            $currencyPeriod->getPeriodData();
        }
        echo PHP_EOL;

        return ExitCode::OK;
    }
}
