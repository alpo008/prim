<?php

namespace app\models;


/**
 * This is the model class for table "currencies".
 *
 * @property int $id
 * @property string $valuteId Идентификатор валюты
 * @property string $numCode Числовой код валюты
 * @property string $charCode Буквенный код валюты
 * @property string $name Наименование валюты
 * @property float|null $value Значение курса
 * @property int $nominal Номинал
 * @property int|null $date Дата публикации курса
 */
class Currency extends \yii\db\ActiveRecord
{
    const EXPECTED_CURRENCIES_NUMBER = 33;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%currencies}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['valuteId', 'numCode', 'charCode', 'name', 'nominal', 'date'], 'required'],
            [['value'], 'number'],
            [['nominal', 'date'], 'integer'],
            [['valuteId'], 'string', 'max' => 15],
            [['numCode', 'charCode'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 255],
            [['numCode', 'date'], 'unique', 'targetAttribute' => ['numCode', 'date']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'valuteId' => 'Идентификатор валюты',
            'numCode' => 'Числовой код валюты',
            'charCode' => 'Буквенный код валюты',
            'name' => 'Наименование валюты',
            'value' => 'Значение курса',
            'nominal' => 'Номинал',
            'date' => 'Дата публикации курса',
        ];
    }

    /**
     * UNIX timestamp хронологически последних записей
     *
     * @return bool|mixed|string|null
     */
    public static function getLastFilledDate()
    {
        return self::find()->max('date');
    }

    /**
     * UNIX timestamp хронологически первых записей
     *
     * @return bool|mixed|string|null
     */
    public static function getFirstFilledDate()
    {
        return self::find()->min('date');
    }
}
