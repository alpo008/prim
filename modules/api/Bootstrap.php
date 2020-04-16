<?php

namespace app\modules\api;

use yii\base\BootstrapInterface;
use yii\web\GroupUrlRule;

class Bootstrap implements BootstrapInterface
{

    public $urlPrefix = 'api';

    /** @var array The rules to be used in URL management. */

    public $urlRules = [
        'GET,HEAD valute\/?' => 'default/index',
        'GET,HEAD valute/<id>/<date_start>/<date_end>\/?' => 'default/view',
        'POST valute' => 'default/create',
        'PUT,PATCH valute' => 'default/update',
        'DELETE valute' => 'default/delete',
    ];
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->urlManager->addRules([
            new GroupUrlRule([
                "prefix" => $this->urlPrefix,
                "rules" => $this->urlRules,
            ]),
        ], false);
    }
}