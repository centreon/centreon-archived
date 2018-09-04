import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//******************************************************go to the widget page******************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_Extensions'))

WebUI.click(findTestObject('Old menu/Administration/a_Widgets'))

WebUI.waitForPageLoad(3)

//*******************************************************Install the widgets*******************************************************//

//While I can install a widget id est while an icon of a cog-wheel is present, I install a widget (I click on the cog-wheel)
while (WebUI.verifyElementPresent(findTestObject('Administration/Extensions/Widget installation/img_installBtn ico-16 margin_r'), 
    1, FailureHandling.OPTIONAL)) {
	//This clicks on a cog-wheel
    WebUI.click(findTestObject('Administration/Extensions/Widget installation/img_installBtn ico-16 margin_r'))

    WebUI.delay(1)

    WebUI.acceptAlert()
	
	WebUI.delay(1)
}

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
