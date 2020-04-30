<?php


namespace app\models\cbr;


use Yii;
use app\models\Currency;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use DateTimeZone;

/**
 * Class CurrencyPeriod
 * @package app\models\cbr
 *
 * @property integer $startTimestamp
 * @property integer $stopTimestamp
 * @property string $url
 *
 * @property integer $tomorrowTimestamp
 */
class CurrencyPeriod extends BaseObject
{
    const DAYS_IN_PERIOD = 45;
    const SECONDS_IN_DAY = 86400;

    public $startTimestamp;
    public $stopTimestamp;
    protected $url;
    public $targetColumns;

    /**
     * CurrencyPeriod constructor.
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        if (empty($config['stopTimestamp']) || empty($config['startTimestamp'])) {
            $this->stopTimestamp = $this->tomorrowTimestamp;
            $this->startTimestamp = $this->stopTimestamp - (self::DAYS_IN_PERIOD * self::SECONDS_IN_DAY);
        }
        $this->targetColumns = ['valuteId', 'date', 'numCode', 'charCode', 'nominal', 'name', 'value'];
        parent::__construct($config);
    }

    /**
     * Сеттер для $url
     * @param string | null $date `php:d/m/Y`
     */
    public function setUrl($date = null)
    {
        if (!$date) {
            $dateTime = new \DateTime();
            $date = $dateTime->format('d/m/Y');
        }
        $url = \Yii::$app->params['currDailyUrl'];
        $this->url = sprintf('%s?date_req=%s', $url, $date);
    }

    /**
     * @return array
     */
    public function request()
    {
        /*$response = file_get_contents($this->url);*/

/*      CURL:
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);

        curl_close($ch);
        return simplexml_load_string($xmlResult);*/

        $client = new Client();
        try {
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($this->url)
                ->send();
        } catch (InvalidConfigException $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
        }
        if (empty($error) && !empty($response)) {
            return $response->data;
        } else {
            return [];
        }
    }


    /**
     * Сохранение в БД данных за период
     */
    public function getPeriodData()
    {
        if (!empty($this->startTimestamp) && !empty($this->stopTimestamp)) {
            $batch = [];
            if ($this->startTimestamp < $this->stopTimestamp) {
                $currentTimestamp = $this->startTimestamp;
                while ($currentTimestamp <= $this->stopTimestamp) {
                    $this->setUrl(date('d/m/Y', $currentTimestamp));
                    try {
                        $dailyResult = $this->request();
                        $date = !empty($dailyResult['@attributes']['Date']) ?
                            (int) Yii::$app->formatter->asTimestamp($dailyResult['@attributes']['Date']) +
                            self::timeOffset() :
                            $currentTimestamp;
                        $batchDaily = !empty($dailyResult['Valute']) ?
                            $this->toBatch($dailyResult['Valute'], $date) :
                            [];
                        //$this->saveBatch($batchDaily);
                        $batch = array_merge($batch, $batchDaily);
                    } catch (InvalidConfigException $e) {
                        Yii::$app->session->setFlash('danger', $e->getMessage());
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('danger', $e->getMessage());
                    }
                    $currentTimestamp += self::SECONDS_IN_DAY;
                }
            }
            $this->saveBatch(array_values($batch));
        }
    }

    /**
     * @param array $data
     * @param string $date
     * @return array
     */
    public function toBatch($data, $date)
    {
        $result = [];
        if (!empty($data) && is_array($data)) {
            foreach ($data as $valutesData) {
                if (!empty($valutesData && is_array($valutesData))) {
                    $entry = [];
                    foreach ($valutesData as $key => $valuteData) {
                        if ($key === '@attributes' && !empty($valuteData['ID'])) {
                            $entry['valuteId'] = $valuteData['ID'];
                        } elseif ($key === 'Value') {
                            $entry['value'] = preg_replace('/,/', '.', $valuteData);
                        } else {
                            $index = lcfirst($key);
                            $entry[$index] = $valuteData;
                        }
                        $entry['date'] = $date;
                    }
                    $result[$entry['valuteId'] . '_' . $date] = $entry;
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param array $batch
     * @return int
     */
    private function saveBatch($batch)
    {
        $result = 0;
        if (empty($batch)) {
            return $result;
        }
        $query = Yii::$app->db->createCommand()->batchInsert(
            Currency::tableName(),
            $this->targetColumns,
            $batch
        );
        try {
             $result = $query->execute();
        } catch (\yii\db\Exception $e) {
        }
        return $result;
    }

    /**
     * UNIX timestamp на начало сегодняшнего дня
     *
     * @return false|int
     */
    public static function getTomorrowTimestamp()
    {
        return strtotime("tomorrow") + self::SECONDS_IN_DAY;
    }

    /**
     * @return int
     */
    public function timeOffset()
    {
        $localTimeZone = new DateTimeZone(Yii::$app->formatter->defaultTimeZone);
        $utc = new DateTimeZone('Europe/London');
        try {
            return $localTimeZone->getOffset(new \DateTime('now', $utc));
        } catch (\Exception $e) {
            return 10800;
        }
    }
}