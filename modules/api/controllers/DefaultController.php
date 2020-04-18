<?php

namespace app\modules\api\controllers;

use app\models\Currency;
use yii\rest\Controller;
use yii\web\Response;

/**
 * Default controller for the `api` module
 */
class DefaultController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            // For cross-domain AJAX request
            'corsFilter'  => [
                'class' => \yii\filters\Cors::className(),
                'cors'  => [
                    'Origin' => ['*'],
                ]
            ]
        ]);
    }

    /**
     * Возвращает список доступных валют
     *
     * @return array
     */
    public function actionIndex()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $list = Currency::find()
            ->select(['DISTINCT(valuteId)', 'name'])
            ->asArray()->all();
        return array_column($list, 'name', 'valuteId');
    }

    /**
     * Возвращает курс выбранной валюты в заданном интервале дат
     *
     * @param string $id
     * @param string $date_start
     * @param string $date_end
     * @return Currency[]|array|string[]
     */
    public function actionView($id, $date_start, $date_end)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $id = trim($id);
        $date_start = trim($date_start);
        $date_end = trim($date_end);
        if ($this->validate($id, $date_start, $date_end)) {
            $valuteId = $id;
            $timestampStart = \Yii::$app->formatter->asTimestamp($date_start);
            $timestampEnd = \Yii::$app->formatter->asTimestamp($date_end);
            $models = Currency::find()
                ->where(compact('valuteId'))
                ->andWhere(['between', 'date', $timestampStart, $timestampEnd])
                ->orderBy('date')
                ->asArray()
                ->all();
            array_walk($models, function (&$entry){
                unset($entry['id']);
                $entry['date'] = \Yii::$app->formatter->asDatetime($entry['date'], 'php:Y-m-d');
            }, $models);
            return $models;
        } else {
            return ['result' => 'NOK', 'message' => 'Некорректный запрос'];
        }
    }

    /**
     * Редактирование записи с заданными идентификатором и датой
     * @return array
     */
    public function actionCreate()
    {
        //TODO Если необходимо, реализовать создание записи.
        $id = \Yii::$app->request->post('id') ?? 'NULL';
        $date = \Yii::$app->request->post('date') ?? 'NULL';
        $value = \Yii::$app->request->post('value') ?? 'NULL';
        $message = sprintf('Может быть создана запись с id = %s за %s с курсом %f', $id, $date, $value);
        $result = 'success';
        return compact('result', 'message');
    }

    /**
     * Редактирование записи с заданными идентификатором и датой
     * @return array
     */
    public function actionUpdate()
    {
        //TODO Если необходимо, реализовать редактирование записи.
        $id = \Yii::$app->request->post('id') ?? 'NULL';
        $date = \Yii::$app->request->post('date') ?? 'NULL';
        $value = \Yii::$app->request->post('value') ?? 'NULL';
        $message = sprintf('Для записи с id = %s за %s может быть установлен курс %f', $id, $date, $value);
        $result = 'success';
        return compact('result', 'message');
    }

    /**
     * Удаление записи по идентификатору и дате
     * @return array
     */
    public function actionDelete()
    {
        //TODO Если необходимо, реализовать удаление записи.
        $id = \Yii::$app->request->post('id') ?? 'NULL';
        $date = \Yii::$app->request->post('date') ?? 'NULL';
        $message = sprintf('Может быть удалена запись с id = %s за %s', $id, $date);
        $result = 'success';
        return compact('result', 'message');
    }

    /**
     * Валидация данных из гет-запроса
     *
     * @param string $valuteId
     * @param string $dateStart
     * @param string $dateEnd
     * @return bool
     */
    private function validate($valuteId, $dateStart, $dateEnd)
    {
        $result = true;
        $re = '/^\w{1}\d{5}\w{0,1}$/i';
        $result &= !!preg_match($re, $valuteId);
        $re = '/^(19|20)\d\d-((0[1-9]|1[012])-(0[1-9]|[12]\d)|(0[13-9]|1[012])-30|(0[13578]|1[02])-31)$/';
        $result &= !!preg_match($re, $dateStart);
        $result &= !!preg_match($re, $dateEnd);
        return $result;
    }
}
