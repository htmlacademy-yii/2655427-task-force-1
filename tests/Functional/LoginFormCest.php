<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\tests\Support\FunctionalTester;

/**
 * Tests the login form.
 */
final class LoginFormCest
{
    /**
     * Opens the login page before each test.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
    }

    /**
     * Checks that the login page opens correctly.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function openLoginPage(FunctionalTester $I)
    {
        $I->see('Login', 'h1');
    }

    /**
     * Demonstrates the amLoggedInAs method using a user ID.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function internalLoginById(FunctionalTester $I)
    {
        $I->amLoggedInAs(100);
        $I->amOnPage('/');
        $I->see('Logout (admin)');
    }

    /**
     * Demonstrates the amLoggedInAs method using a user instance.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function internalLoginByInstance(FunctionalTester $I)
    {
        $I->amLoggedInAs(\app\models\User::findByUsername('admin'));
        $I->amOnPage('/');
        $I->see('Logout (admin)');
    }

    /**
     * Checks validation errors when login credentials are empty.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function loginWithEmptyCredentials(FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    /**
     * Checks validation when incorrect login credentials are submitted.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function loginWithWrongCredentials(FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password.');
    }

    /**
     * Checks that a user can log in successfully with valid credentials.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function loginSuccessfully(FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'admin',
        ]);
        $I->see('Logout (admin)');
        $I->dontSeeElement('form#login-form');
    }
}
