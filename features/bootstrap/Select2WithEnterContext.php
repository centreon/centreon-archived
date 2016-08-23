<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;

/**
 * Defines application features from the specific context.
 */
class Select2WithEnterContext extends CentreonContext
{
    /**
     * @Given I search on a select2
     */
    public function iSearchOnASelect2()
    {
        /* Go to the page to connector configuration page */
        $this->visit('/main.php?p=60806&o=c&id=0');


        $this->assertFind('css', '#command_line')->click();

        $key = 'a';
        $script = "jQuery.event.trigger({ type : 'keypress', which : '" . $key . "' });";
        $this->getSession()->executeScript($script);



        file_put_contents('/tmp/test.png', $this->getSession()->getDriver()->getScreenshot());



        throw new Exception('tt');

        /* Wait page loaded */
        $this->spin(
            function ($context) {
                return $context->getSession()->getPage()->has(
                    'css',
                    'input[name="submitC"]'
                );
            },
            30
        );

        /* Add search to select2 */
        $inputField = $this->assertFind('css', 'select#command_id');

        /* Open the select2 */
        $choice = $inputField->getParent()->find('css', '.select2-selection');
        if (!$choice) {
            throw new \Exception('No select2 choice found');
        }
        $choice->press();

        $this->spin(
            function ($context) {
                return count($context->getSession()->getPage()->findAll('css', '.select2-container--open li.select2-results__option')) >= 4;
            },
            30
        );




/*
        $id ='command_line';
        $key = 'a';

        $this->getSession()->getPage()->findAll('css', '#command_line')->keyPress($key);
*/

/*
        $key = 'a';
        $script = "jQuery.event.trigger({ type : 'keypress', which : '" . $key . "' });";
        $this->getSession()->evaluateScript($script);
*/


/*

        $this->getSession()->executeScript("
            $(\"select#command_id\").trigger($.Event('keypress', {which: 40, keyCode: 40}));
        ");


*/

        //$xpath = '//html/body/table/thead/tr/th[first()]';

 //       $this->getSession()->getDriver()->keyPress('//*[@class="select2-search__field', 'e');


        $this->getSession()->getDriver()->keyPress('//*[@id="command_line"]', 'a');

        file_put_contents('/tmp/test.png', $this->getSession()->getDriver()->getScreenshot());


  //      $inputField->getPress('13');



/*
        $script = "
        var keyboardEvent = document.createEvent(\"KeyboardEvent\");
        var initMethod = typeof keyboardEvent.initKeyboardEvent !== 'undefined' ? \"initKeyboardEvent\" : \"initKeyEvent\";
keyboardEvent[initMethod](
    \"keydown\", // event type : keydown, keyup, keypress
                    true, // bubbles
                    true, // cancelable
                    window, // viewArg: should be window
                    false, // ctrlKeyArg
                    false, // altKeyArg
                    false, // shiftKeyArg
                    false, // metaKeyArg
                    40, // keyCodeArg : unsigned long the virtual key code, else 0
                    0 // charCodeArgs : unsigned long the Unicode character associated with the depressed key, else 0
);
document.dispatchEvent(keyboardEvent);";

*/

/*
        $script = 'var e = jQuery.Event("keypress");';
        $script .= 'e.which = 13;';
        $script .= '$("select#command_id").trigger(e);';

        $this->getSession()->evaluateScript($script);
        $this->getSession()->executeScript($script);
*/

/*
        $this->getSession()->executeScript(
            'var e = jQuery.Event("keypress");
            e.which = 40;
            e.keyCode = 40;
            $(".select2-search__field").trigger(e);'
        );

        $this->getSession()->executeScript(
            'var e = jQuery.Event("keypress");
            e.which = 40;
            e.keyCode = 40;
            $("select#command_id").trigger(e);'
        );
*/

/*
        $this->getSession()->executeScript(
            'var press = jQuery.Event("keypress");
        press.ctrlKey = false;
        press.which = 13;
        $("select#command_id").trigger(press);'
        );
*/


sleep(1);

        throw new Exception('tt');

        $this->getSession()->wait(1000);

    }

    /**
     * @When research give results
     */
    public function researchGiveResults()
    {

        1 == 1;

    }

    /**
     * @Then I can use ENTER key to validate my choice
     */
    public function iCanUseEnterKeyToValidateMyChoice()
    {

        1 == 1;

    }

}
