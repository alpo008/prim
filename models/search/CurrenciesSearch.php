<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Currency;

/**
 * CurrenciesSearch представляет модель для поиска объектов класса `app\models\Currency`.
 *
 * @property array $nominalFilter
 */
class CurrenciesSearch extends Currency
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['valuteId', 'numCode', 'charCode', 'name', 'nominal', 'date'], 'safe']
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

        $query->andFilterWhere(['like', 'valuteId', $this->valuteId])
            ->andFilterWhere(['like', 'numCode', $this->numCode])
            ->andFilterWhere(['like', 'charCode', $this->charCode])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'FROM_UNIXTIME(date, "%Y-%m-%d")', $this->date]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getNominalFilter()
    {
        $nominals = $this->find()
            ->select('nominal')
            ->distinct()
            ->orderBy('nominal')
            ->asArray()
            ->all();
        return array_column($nominals, 'nominal', 'nominal');
    }
}