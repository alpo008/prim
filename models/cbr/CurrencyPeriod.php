<?php


namespace app\models\cbr;


use Yii;
use app\models\Currency;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class CurrencyPeriod
 * @package app\models\cbr
 *
 * @property integer $startTimestamp
 * @property integer $stopTimestamp
 * @property string $url
 */
class CurrencyPeriod extends BaseObject
{
    use CBRTrait;

    const DAYS_IN_PERIOD = 31;
    const SECONDS_IN_DAY = 86400;

    public $startTimestamp;
    public $stopTimestamp;
    protected $url;

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
     * @return \SimpleXMLElement
     */
    public function request()
    {
        $xmlResult = file_get_contents($this->url);
        return simplexml_load_string($xmlResult);
    }


    /**
     * Сохранение в БД данных за период
     */
    public function getPeriodData()
    {
        if (!empty($this->startTimestamp) && !empty($this->stopTimestamp)) {
            if ($this->startTimestamp < $this->stopTimestamp) {
                $currentTimestamp = $this->startTimestamp;
                while ($currentTimestamp <= $this->stopTimestamp) {
                    $this->setUrl(date('d/m/Y', $currentTimestamp));
                    $xmlDaily = $this->request();
                    try {
                        $dataDaily = $this->toArray($xmlDaily);
                        if (!empty($dataDaily) && is_array($dataDaily)) {
                            foreach ($dataDaily as $data) {
                                if (!empty($data) && is_array($data)) {
                                    $currency = new Currency($data);
                                    $currency->save();
                                }
                            }
                        }
                    } catch (InvalidConfigException $e) {
                        Yii::$app->session->setFlash('danger', $e->getMessage());
                    }
                    $currentTimestamp += self::SECONDS_IN_DAY;
                }
            }
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function toArray(\SimpleXMLElement $xml)
    {
        $result = [];
        if ($xml instanceof \SimpleXMLElement) {
            try {
                $date = (string)$xml->attributes()['Date'];
            } catch (\Exception $e) {
                $date = \Yii::$app->formatter->asDate(time());
            }
            $date = \Yii::$app->formatter->asTimestamp($date);
            foreach ($xml->children() as $child) {
                $valuteId = (string) $child->attributes()['ID'];
                if ($child instanceof \SimpleXMLElement) {
                    $numCode = (string)$child->NumCode;
                    $charCode = (string)$child->CharCode;
                    $name = (string)$child->Name;
                    $nominal = (string)$child->Nominal;
                    $value = (string)$child->Value;
                    $result[$valuteId] = compact(
                        'valuteId', 'date', 'valuteId', 'numCode', 'charCode', 'name',
                        'nominal', 'value'
                    );
                }
            }
        }
        return $result;
    }
}