<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Currency;

/**
 * CurrenciesSearch представляет модель для поиска объектов класса `app\models\Currency`.
 *
 * @property array $nominalFilter
 * @property array $names
 *
 * @property string $maxFilledDate
 * @property string $minFilledDate
 */
class CurrenciesSearch extends Currency
{
    const DEF_DATE_FROM = '2020-01-01';
    const DEF_DATE_TO = '2029-01-01';

    public $dateFrom;
    public $dateTo;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['valuteId', 'numCode', 'charCode', 'name', 'nominal', 'date', 'dateFrom', 'dateTo',], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Currency::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => static::EXPECTED_CURRENCIES_NUMBER,
            ],
            'sort' => [
                'defaultOrder' => ['date'=>SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['=', 'nominal', $this->nominal]);
        $query->andFilterWhere(['in', 'name', $this->name]);

        $query->andFilterWhere(['like', 'valuteId', $this->valuteId])
            ->andFilterWhere(['like', 'numCode', $this->numCode])
            ->andFilterWhere(['like', 'charCode', $this->charCode])
            ->andFilterWhere(['between', 'FROM_UNIXTIME(date, "%Y-%m-%d")', $this->dateFrom, $this->dateTo]);

        return $dataProvider;
    }

    /**
     * Фильтр для номинала ввалюты
     *
     * @return array
     */
    public function getNominalFilter()
    {
        $nominalsQry = $this->find()
            ->select('nominal')
            ->distinct();
            if (!empty($this->name)) {
                $nominalsQry->where(['name' => $this->name]);
            }
            $nominals = $nominalsQry->orderBy('nominal')
            ->asArray()
            ->all();
        return array_column($nominals, 'nominal', 'nominal');
    }

    /**
     * Фильтр для наименования валюты
     *
     * @return array
     */
    public function getCurrencyFilter()
    {
        $currenciesQry = $this->find()
            ->select('name')
            ->distinct();
        if (!empty($this->nominal)) {
            $currenciesQry->where(['nominal' => $this->nominal]);
        }
        $currencies = $currenciesQry->orderBy('name')
        ->asArray()
        ->all();
        return array_column($currencies, 'name', 'name');
    }

    /**
     * Дата хронологически первых записей в формате `php:Y-m-d`
     *
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getMinFilledDate()
    {
        if (!$minTimestamp = static::getFirstFilledDate()) {
            return self::DEF_DATE_FROM;
        }
        return \Yii::$app->formatter->asDatetime($minTimestamp, 'php:Y-m-d');
    }

    /**
     * Дата хронологически последних записей в формате `php:Y-m-d`
     *
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getMaxFilledDate()
    {
        if (!$maxTimestamp = static::getLastFilledDate()) {
            return self::DEF_DATE_TO;
        }
        return \Yii::$app->formatter->asDatetime($maxTimestamp, 'php:Y-m-d');
    }
}