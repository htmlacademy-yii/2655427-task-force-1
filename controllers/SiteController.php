<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\City;
use app\models\ContactForm;
use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
use yii\base\Module;
use yii\base\Security;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\mail\MailerInterface;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

/**
 * Handles requests for the site.
 */
class SiteController extends Controller
{
    public function __construct(
        string $id,
        Module $module,
        private readonly MailerInterface $mailer,
        private readonly Security $security,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Returns controller behaviors.
     *
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Returns external actions.
     *
     * @return array
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    /**
     * Displays the landing page.
     *
     * @return string|Response
     */
    public function actionIndex(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['tasks/index']);
        }

        $this->layout = 'landing';

        return $this->render('landing');
    }

    /**
     * Logs a user in.
     *
     * @return string|Response|array
     */
    public function actionLogin(): string|Response|array
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm($this->security);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->load($this->request->post(), 'LoginForm') && $model->login()) {
                return [
                    'success' => true,
                ];
            }

            return [
                'success' => false,
                'errors' => $model->getErrors(),
            ];
        }

        if ($model->load($this->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Handles successful authentication through an external provider.
     *
     * @param ClientInterface $client Authentication client.
     *
     * @return void
     */
    public function onAuthSuccess(ClientInterface $client): void
    {
        $attributes = $client->getUserAttributes();

        $githubId = (int) $attributes['id'];
        $email = $attributes['email'] ?? null;

        $user = User::findOne(['github_id' => $githubId]);

        if ($user === null && $email !== null) {
            $user = User::findOne(['email' => $email]);

            if ($user !== null) {
                $user->github_id = $githubId;
                $user->save(false);
            }
        }

        if ($user === null) {
            $city = City::findOne(['name' => $attributes['location'] ?? '']);

            if ($city === null) {
                $city = City::find()->one();
            }

            $user = new User();
            $user->github_id = $githubId;
            $user->email = $email ?: $githubId . '@github.local';
            $user->name = $attributes['name'] ?? $attributes['login'];
            $user->city_id = $city->id;
            $user->user_role = User::USER_ROLE_CUSTOMER;
            $user->avatar_path = $attributes['avatar_url'] ?? null;
            $user->save(false);
        }

        Yii::$app->user->login($user);

        $this->redirect(['tasks/index']);
    }

    /**
     * Logs the current user out.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays the contact page.
     *
     * @return string|Response
     */
    public function actionContact(): string|Response
    {
        $model = new ContactForm();

        if ($model->load($this->request->post()) && $model->validate()) {
            if ($this->mailer->compose()
                ->setTo('admin@example.com')
                ->setFrom([$model->email => $model->name])
                ->setSubject($model->subject)
                ->setTextBody($model->body)
                ->send()
            ) {
                Yii::$app->session->setFlash(
                    'contactFormSubmitted',
                    'Thank you for contacting us. We will respond to you as soon as possible.'
                );
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays the about page.
     *
     * @return string
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }
}
