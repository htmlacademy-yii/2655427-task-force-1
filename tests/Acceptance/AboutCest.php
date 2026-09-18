<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use yii\helpers\Url;

/**
 * Tests the About page.
 */
final class AboutCest
{
    /**
     * Checks that the About page is displayed correctly.
     *
     * @param AcceptanceTester $I Acceptance tester.
     */
    public function ensureThatAboutWorks(AcceptanceTester $I): void
    {
        $I->amOnPage(Url::toRoute('/site/about'));
        $I->see('About', 'h1');
    }
}
