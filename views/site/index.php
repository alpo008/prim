<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CurrenciesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Курсы на сегодня';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="currencies-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить', ['update-table'], ['class' => 'btn btn-default']) ?>
    </p>

    <?php Pjax::begin(['id' => 'currencies-index__pjax-container']); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'valuteId',
                'numCode',
                'charCode',
                'name',
                'value',
                [
                    'attribute' => 'nominal',
                    'filter' => $searchModel->nominalFilter,
                    'headerOptions' => ['style' => 'min-width:120px;']
                ],
                [
                    'attribute' => 'date',
                    'value' => function($model) {
                        return date('Y-m-d', $model->date);
                    }
                ]
            ]
        ]);
        ?>
    <?php Pjax::end(); ?>
</div>
