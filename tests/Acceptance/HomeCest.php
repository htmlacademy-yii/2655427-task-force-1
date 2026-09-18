<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use yii\helpers\Url;

/**
 * Tests the home page.
 */
final class HomeCest
{
    /**
     * Checks that the home page works correctly.
     *
     * @param AcceptanceTester $I Acceptance tester.
     */
    public function ensureThatHomePageWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/index'));
        $I->see(\Yii::$app->name);

        $I->seeLink('About');
        $I->click('About');

        $I->see('This is the About page.');
    }
}
