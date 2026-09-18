<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use yii\helpers\Url;

/**
 * Tests the Contact page.
 */
final class ContactCest
{
    /**
     * Opens the Contact page before each test.
     *
     * @param AcceptanceTester $I Acceptance tester.
     */
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/contact'));
    }

    /**
     * Checks that the Contact page is displayed correctly.
     *
     * @param AcceptanceTester $I Acceptance tester.
     */
    public function contactPageWorks(AcceptanceTester $I)
    {
        $I->wantTo('ensure that contact page works');
        $I->see('Contact', 'h1');
    }

    /**
     * Checks that the contact form can be submitted with valid data.
     *
     * @param AcceptanceTester $I Acceptance tester.
     */
    public function contactFormCanBeSubmitted(AcceptanceTester $I)
    {
        $I->amGoingTo('submit contact form with correct data');
        $I->fillField('#contactform-name', 'tester');
        $I->fillField('#contactform-email', 'tester@example.com');
        $I->fillField('#contactform-subject', 'test subject');
        $I->fillField('#contactform-body', 'test content');
        $I->fillField('#contactform-verifycode', 'testme');

        $I->click('contact-button');

        $I->dontSeeElement('#contact-form');
        $I->see('Thank you for contacting us. We will respond to you as soon as possible.');
    }
}
