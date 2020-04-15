<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\Currency]].
 *
 * @see \app\models\Currency
 */
class CurrenciesQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Currency[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Currency|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * По числовому коду
     *
     * @param int $numCode
     * @return CurrenciesQuery
     */
    public function byNumCode($numCode)
    {
        return $this->andWhere(['numCode' => $numCode]);
    }

    /**
     * По дате
     *
     * @param int $date
     * @return CurrenciesQuery
     */
    public function byDate($date)
    {
        return $this->andWhere(['date' => $date]);
    }
}
