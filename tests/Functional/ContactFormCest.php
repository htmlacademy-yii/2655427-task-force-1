<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\tests\Support\FunctionalTester;

/**
 * Tests the contact form.
 */
final class ContactFormCest
{
    /**
     * Opens the Contact page before each test.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/contact');
    }

    /**
     * Checks that the Contact page opens correctly.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function openContactPage(FunctionalTester $I)
    {
        $I->see('Contact', 'h1');
    }

    /**
     * Checks validation errors when the form is submitted empty.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function submitEmptyForm(FunctionalTester $I)
    {
        $I->submitForm('#contact-form', []);
        $I->expectTo('see validations errors');
        $I->see('Contact', 'h1');
        $I->see('Name cannot be blank');
        $I->see('Email cannot be blank');
        $I->see('Subject cannot be blank');
        $I->see('Body cannot be blank');
        $I->see('The verification code is incorrect');
    }

    /**
     * Checks validation when an invalid email address is submitted.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function submitFormWithIncorrectEmail(FunctionalTester $I)
    {
        $I->submitForm('#contact-form', [
            'ContactForm[name]' => 'tester',
            'ContactForm[email]' => 'tester.email',
            'ContactForm[subject]' => 'test subject',
            'ContactForm[body]' => 'test content',
            'ContactForm[verifyCode]' => 'testme',
        ]);
        $I->expectTo('see that email address is wrong');
        $I->dontSee('Name cannot be blank', '.help-inline');
        $I->see('Email is not a valid email address.');
        $I->dontSee('Subject cannot be blank', '.help-inline');
        $I->dontSee('Body cannot be blank', '.help-inline');
        $I->dontSee('The verification code is incorrect', '.help-inline');
    }

    /**
     * Checks that the contact form can be submitted successfully.
     *
     * @param FunctionalTester $I Functional tester.
     */
    public function submitFormSuccessfully(FunctionalTester $I)
    {
        $I->submitForm('#contact-form', [
            'ContactForm[name]' => 'tester',
            'ContactForm[email]' => 'tester@example.com',
            'ContactForm[subject]' => 'test subject',
            'ContactForm[body]' => 'test content',
            'ContactForm[verifyCode]' => 'testme',
        ]);
        $I->seeEmailIsSent();
        $I->dontSeeElement('#contact-form');
        $I->see('Thank you for contacting us. We will respond to you as soon as possible.');
    }
}
