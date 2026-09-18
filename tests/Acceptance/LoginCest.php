<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use yii\helpers\Url;

/**
 * Tests the login page.
 */
final class LoginCest
{
    /**
     * Checks that a user can log in with valid credentials.
     *
     * @param AcceptanceTester $I Acceptance tester.
     */
    public function ensureThatLoginWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/login'));
        $I->see('Login', 'h1');

        $I->amGoingTo('try to login with correct credentials');
        $I->fillField('input[name="LoginForm[username]"]', 'admin');
        $I->fillField('input[name="LoginForm[password]"]', 'admin');
        $I->click('login-button');

        $I->expectTo('see user info');
        $I->see('Logout');
    }
}
