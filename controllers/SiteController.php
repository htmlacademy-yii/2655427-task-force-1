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
    /**
     * SiteController constructor.
     *
     * @param string $id Controller ID.
     * @param Module $module Parent module.
     * @param MailerInterface $mailer Mailer component.
     * @param Security $security Security component.
     * @param array $config Controller configuration.
     */
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

            if (
                $model->load($this->request->post(), 'LoginForm')
                && $model->login()
            ) {
                return [
                    'success' => true,
                ];
            }

            return [
                'success' => false,
                'errors' => $model->getErrors(),
            ];
        }

        if (
            $model->load($this->request->post())
            && $model->login()
        ) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Handles successful authentication through GitHub.
     *
     * @param ClientInterface $client Authentication client.
     *
     * @return void
     */
    public function onAuthSuccess(ClientInterface $client): void
    {
        $attributes = $client->getUserAttributes();

        if (!isset($attributes['id'])) {
            return;
        }

        $githubId = (int) $attributes['id'];
        $email = $attributes['email'] ?? null;

        $user = User::findOne(['github_id' => $githubId]);

        if ($user === null && $email !== null) {
            $user = User::findOne(['email' => $email]);

            if ($user !== null) {
                $user->github_id = $githubId;

                if (!$user->save()) {
                    return;
                }
            }
        }

        if ($user === null) {
            $user = $this->createGithubUser(
                $attributes,
                $githubId,
                $email
            );
        }

        if ($user === null) {
            return;
        }

        if (!Yii::$app->user->login($user)) {
            return;
        }
    }

    /**
     * Creates a user from GitHub account data.
     *
     * @param array $attributes GitHub user attributes.
     * @param int $githubId GitHub user ID.
     * @param string|null $email GitHub email address.
     *
     * @return User|null
     */
    private function createGithubUser(
        array $attributes,
        int $githubId,
        ?string $email
    ): ?User {
        $name = $attributes['name'] ?? $attributes['login'] ?? null;

        if ($name === null) {
            return null;
        }

        $cityName = $attributes['location'] ?? '';
        $city = City::findOne(['name' => $cityName]) ?? City::find()->one();

        if ($city === null) {
            return null;
        }

        $user = new User();
        $user->github_id = $githubId;
        $user->email = $email ?? $githubId . '@github.local';
        $user->name = $name;
        $user->city_id = $city->id;
        $user->setRole(User::USER_ROLE_CUSTOMER);

        if (!$user->save()) {
            return null;
        }

        return $user;
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
            if (
                $this->mailer->compose()
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
