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

//********************************************************Login as an admin********************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

WebUI.waitForPageLoad(3)

//*********************************************************Go to My Account********************************************************//

WebUI.click(findTestObject('Administration/button_Administration'))

WebUI.mouseOver(findTestObject('Administration/Parameters/span_Parameters'))

WebUI.click(findTestObject('Administration/Parameters/p_My Account'))

//*****************************************************Change the default page*****************************************************//

WebUI.delay(1)

WebUI.selectOptionByLabel(findTestObject('Administration/Parameters/select_Default page'), 'Configuration > Hosts > Hosts', false)

WebUI.delay(5)

WebUI.click(findTestObject('General/input_submitC'))

//Wait to be sure that Edge correctly modifies the parameters
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

//********************************************************Login as an admin********************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

WebUI.waitForPageLoad(3)

//******************************************************Check the current page*****************************************************//

WebUI.verifyMatch(WebUI.getUrl(), config.getValue('url', 1) + 'main.php?p=60101', false)

WebUI.verifyElementPresent(findTestObject('General/input_Search'), 1)

def hostFile = TestDataFactory.findTestData('Host data')

def element = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1', true)

WebUI.verifyElementPresent(element, 1)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
