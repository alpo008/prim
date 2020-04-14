<?php


namespace app\models\cbr;


/**
 * Trait CBRTrait
 * @package app\models\cbr
 *
 * @property string $url
 */
trait CBRTrait
{
    private $url;

    public function request()
    {
        $xmlResult = file_get_contents($this->url);
        $simpleXml = simplexml_load_string($xmlResult);
        return $this->toArray($simpleXml);
    }

    /**
     * Сеттер для $url
     */
    public function setUrl()
    {
        $dateTime = new \DateTime();
        $date2 = $date = $dateTime->format('d/m/Y');
        $date1 = $dateTime->modify('-1 month')->format('d/m/Y');
        switch ($this->className()) {
            case 'app\models\cbr\CurrencyDaily' :
                $url = \Yii::$app->params['currDailyUrl'];
                $this->url = sprintf('%s?date_req=%s', $url, $date);
            break;
            case 'app\models\cbr\CurrPeriodic' :
                $url = \Yii::$app->params['currPeriodUrl'];
                $this->url = sprintf('%s?date_req1=%s&date_req2=%s', $url, $date1, $date2);
            break;
        }
    }
}