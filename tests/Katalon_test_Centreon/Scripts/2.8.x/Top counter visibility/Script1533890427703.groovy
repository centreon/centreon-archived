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

//This file contains the url and the admin's login and password
def config = TestDataFactory.findTestData('Configuration')

def user = TestDataFactory.findTestData('user config')

def acla = TestDataFactory.findTestData('ACL action')

WebUI.openBrowser(config.getValue('url', 1))

//**********************************************First step : top counter with pollers**********************************************//

//********************************************************Login as an admin********************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*********************************************************go to ACL action********************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

WebUI.click(findTestObject('Old menu/Administration/a_Actions Access'))

//******************************************************Modify the acl action******************************************************//

WebUI.delay(1)

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals',
	config.getValue('TimeIndicator', 1) + acla.getValue(1, 1), true)

WebUI.click(a)

WebUI.delay(1)

WebUI.click(findTestObject('Administration/ACL/ACL action creation/input_Display top counter'))

WebUI.click(findTestObject('Administration/ACL/ACL action creation/input_Display poller statistics'))

WebUI.click(findTestObject('General/input_submitC'))

WebUI.click(findTestObject('Old menu/span_Logout'))

WebUI.waitForPageLoad(3)

//******************************************************Login as a simple user*****************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'),
	config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1')
	
WebUI.setText(findTestObject('General/Login/input_password'), user.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

//******************************************************Verify the top counter*****************************************************//

WebUI.delay(1)

WebUI.verifyElementPresent(findTestObject('Top counter/Top counter_Host'), 3)

WebUI.verifyElementPresent(findTestObject('Top counter/Top counter_First icon'), 3)

WebUI.click(findTestObject('Old menu/span_Logout'))

//********************************************Second step : top counter without pollers********************************************//

//********************************************************Login as an admin********************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*********************************************************go to ACL action********************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

WebUI.click(findTestObject('Old menu/Administration/a_Actions Access'))

//******************************************************Modify the acl action******************************************************//

WebUI.delay(1)

WebUI.click(a)

WebUI.delay(1)

WebUI.click(findTestObject('Administration/ACL/ACL action creation/input_Display poller statistics'))

WebUI.click(findTestObject('General/input_submitC'))

WebUI.click(findTestObject('Old menu/span_Logout'))

//******************************************************Login as a simple user*****************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'),
	config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1')
	
WebUI.setText(findTestObject('General/Login/input_password'), user.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

WebUI.waitForPageLoad(3)

//******************************************************Verify the top counter*****************************************************//

WebUI.delay(1)

WebUI.verifyElementPresent(findTestObject('Top counter/Top counter_Host'), 1)

WebUI.verifyElementNotPresent(findTestObject('Top counter/Top counter_First icon'), 1)

WebUI.click(findTestObject('Old menu/span_Logout'))

//*********************************************Third step : pollers without top counter********************************************//

//********************************************************Login as an admin********************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

WebUI.waitForPageLoad(3)

//*********************************************************go to ACL action********************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

WebUI.click(findTestObject('Old menu/Administration/a_Actions Access'))

//******************************************************Modify the acl action******************************************************//

WebUI.delay(1)

WebUI.click(a)

WebUI.delay(1)

WebUI.click(findTestObject('Administration/ACL/ACL action creation/input_Display top counter'))

WebUI.click(findTestObject('Administration/ACL/ACL action creation/input_Display poller statistics'))

WebUI.click(findTestObject('General/input_submitC'))

WebUI.click(findTestObject('Old menu/span_Logout'))

//******************************************************Login as a simple user*****************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'),
	config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1')
	
WebUI.setText(findTestObject('General/Login/input_password'), user.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

WebUI.waitForPageLoad(3)

//******************************************************Verify the top counter*****************************************************//

WebUI.delay(1)

WebUI.verifyElementNotPresent(findTestObject('Top counter/Top counter_Host'), 1)

WebUI.verifyElementPresent(findTestObject('Top counter/Top counter_First icon'), 1)

WebUI.click(findTestObject('Old menu/span_Logout'))

//***************************************************Fourth step : no top counter**************************************************//

//********************************************************Login as an admin********************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

WebUI.waitForPageLoad(3)

//*********************************************************go to ACL action********************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

WebUI.click(findTestObject('Old menu/Administration/a_Actions Access'))

//******************************************************Modify the acl action******************************************************//

WebUI.delay(1)

WebUI.click(a)

WebUI.delay(1)

WebUI.click(findTestObject('Administration/ACL/ACL action creation/input_Display poller statistics'))

WebUI.click(findTestObject('General/input_submitC'))

WebUI.click(findTestObject('Old menu/span_Logout'))

//******************************************************Login as a simple user*****************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'),
	config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1')
	
WebUI.setText(findTestObject('General/Login/input_password'), user.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

WebUI.waitForPageLoad(3)

//******************************************************Verify the top counter*****************************************************//

WebUI.delay(1)

WebUI.verifyElementNotPresent(findTestObject('Top counter/Top counter_Host'), 1)

WebUI.verifyElementNotPresent(findTestObject('Top counter/Top counter_First icon'), 1)

WebUI.click(findTestObject('Old menu/span_Logout'))

WebUI.closeBrowser()
