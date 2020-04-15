<?php

use yii\db\Migration;

/**
 * Class m200415_083540_create_table_currencies
 *
 * @property string $tableName
 */
class m200415_083540_create_table_currencies extends Migration
{
    public $tableName = '{{%currencies}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql' ?
            "ENGINE = INNODB,
            CHARACTER SET utf8,
            COLLATE utf8_unicode_ci,
            COMMENT = 'Курсы валют ЦБ РФ'" :
            null;

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'valuteId' => $this->string(15)->notNull()->comment('Идентификатор валюты'),
            'numCode' => $this->string(4)->notNull()->comment('Числовой код валюты'),
            'charCode' => $this->string(4)->notNull()->comment('Буквенный код валюты'),
            'name' => $this->string(255)->notNull()->comment('Наименование валюты'),
            'value' => $this->decimal(7, 4)->defaultValue(null)->comment('Значение курса'),
            'nominal' => $this->integer()->notNull()->comment('Номинал'),
            'date' => $this->integer()->notNull()->comment('Дата публикации курса'),
        ], $tableOptions);

        $this->createIndex('uk_currencies_numCode_date', $this->tableName,
            ['numCode', 'date'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('uk_currencies_numCode_date', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
