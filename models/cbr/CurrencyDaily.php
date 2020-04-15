<?php


namespace app\models\cbr;

use yii\base\BaseObject;

/**
 * Class CurrencyDaily
 * @package app\models\cbr
 */
class CurrencyDaily extends BaseObject
{
    use CBRTrait;

    /**
     * CurrencyDaily constructor.
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->setUrl();
    }

    protected function parse($xml, &$return, $path='', $root=false)
    {
        $children = array();
        if ($xml instanceof \SimpleXMLElement) {
            $children = $xml->children();
            if ($root){ // we're at root
                $path .= '/'.$xml->getName();
            }
        }
        if ( count($children) == 0 ){
            $return[$path] = (string)$xml;
            return;
        }
        $seen=array();
        foreach ($children as $child => $value) {
            $childname = ($child instanceof \SimpleXMLElement)?$child->getName():$child;
            if ( !isset($seen[$childname])){
                $seen[$childname]=0;
            }
            $seen[$childname]++;
            $this->parse($value, $return, $path.'/'.$child.'['.$seen[$childname].']');
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function toArray(\SimpleXMLElement $xml)
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