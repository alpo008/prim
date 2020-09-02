<?php

namespace app\controllers;

use Yii;
use app\models\cbr\CurrencyPeriod;
use app\models\search\CurrenciesSearch;
use app\models\Currency;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'index', 'update-table', 'empty-table'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'update-table', 'empty-table'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CurrenciesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', compact('searchModel', 'dataProvider'));
    }

    /**
     * Обновление таблицы
     */
    public function actionUpdateTable()
    {
        if ($startTimestamp = Currency::getLastFilledDate()) {
            $startTimestamp += CurrencyPeriod::SECONDS_IN_DAY;
        }
        $stopTimestamp = CurrencyPeriod::getTomorrowTimestamp();
        if (!$startTimestamp || !$stopTimestamp) {
            $currencyPeriod = new CurrencyPeriod();
        } elseif ($stopTimestamp >= $startTimestamp) {
            $currencyPeriod = new CurrencyPeriod(compact('startTimestamp', 'stopTimestamp'));
        }
        if (isset($currencyPeriod)) {
            $currencyPeriod->getPeriodData();
        }
        return $this->redirect(['index']);
    }

    /**
     * Полная очистка таблицы
     *
     * @return Response
     */
    public function actionEmptyTable()
    {
        $command = Yii::$app->db->createCommand(sprintf("TRUNCATE TABLE %s", Currency::tableName()));
        try {
            $command->execute();
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
        }
        return $this->redirect(['index']);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}