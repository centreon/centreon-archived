<?php

use Centreon\Test\Behat\CentreonContext;

class NewHeaderFeatureFlippingContext extends CentreonContext
{
    /**
     * @When I accept the new header feature
     */
    public function IAcceptTheNewHeaderFeature()
    {
        $this->enableNewFeature(true);
    }

    /**
     * @When I decline the new header feature
     */
    public function IDeclineTheNewHeaderFeature()
    {
        $this->enableNewFeature(false);
    }

    /**
     * @Then I see the new header
     */
    public function ISeeTheNewHeader()
    {
        $this->visit('/');
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    '#header-react'
                );
            },
            'New header not found.',
            30
        );
    }

    /**
     * @Then I see the legacy header
     */
    public function ISeeTheLegacyHeader()
    {
        $this->visit('/');
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    '#header'
                );
            },
            'Legacy header not found.',
            10
        );
    }

    /**
     * @When I change the version of header in my profile to :version
     */
    public function IChangeTheVersionOfHeaderInMyProfileTo($version)
    {
        $value = $version === 'legacy' ? 0 : 1;
        $this->visit('/main.php?p=50104&o=c');
        $this->assertFind(
            'css',
            'input[name="features[Header][2]"][value="' . $value . '"]'
        )->click();
        $this->assertFind(
            'css',
            'input[name="submitC"]'
        )->click();
    }
}
