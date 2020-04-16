<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use dosamigos\datepicker\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CurrenciesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Курсы валют';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs(/** @lang JavaScript */ "
    const aoff = () => {
        jQuery('#currencies-index__table-filters input[type=text]').attr('autocomplete', 'off');
    };
    aoff();
    $(document).on('pjax:complete', () => {
        aoff();
    });
", $this::POS_END);

?>
<div class="currencies-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить', ['update-table'], ['class' => 'btn btn-default']) ?>
        <?= Html::a('Сбросить фильтры', '#', [
                'class' => 'btn btn-default',
                'onclick' => /** @lang JavaScript */ "
                    event.preventDefault();
                    let uri = window.location.toString();
                    if (uri.indexOf(\"?\") > 0) {
                        var clean_uri = uri.substring(0, uri.indexOf(\"?\"));
                        window.history.replaceState({}, document.title, clean_uri);
                        $.pjax.reload('#currencies-index__pjax-container');
                    }
                "
            ])
        ?>
    </p>

    <?php Pjax::begin(['id' => 'currencies-index__pjax-container'/*, 'enablePushState' => false*/]); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'currencies-index__table',
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'valuteId',
                'numCode',
                'charCode',
                [
                    'attribute' => 'name',
                    'filter' => $searchModel->getCurrencyFilter()
                ],
                'value',
                [
                    'attribute' => 'nominal',
                    'filter' => $searchModel->nominalFilter,
                    'headerOptions' => ['style' => 'min-width:120px;']
                ],
                [
                    'attribute' => 'date',
                    'value' => function($model) {
                        return Yii::$app->formatter->asDatetime($model->date, 'php:Y-m-d');
                    },
                    'filter' => DateRangePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'dateFrom',
                        'attributeTo' => 'dateTo',
                        'labelTo'=>'по',
                        'language' => 'ru',
                        'size' => 'sm',
                        'clientOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd' ,
                            'autoUpdateInput' => false,
                            'keepEmptyValues' => true,
                            'startDate' => $searchModel->minFilledDate,
                            'endDate' => $searchModel->maxFilledDate,
                            'autocomplete' =>"off",
                        ]
                    ])
                ]
            ]
        ]);
        ?>
    <?php Pjax::end(); ?>
</div>
