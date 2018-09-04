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
import org.openqa.selenium.Keys as Keys

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*****************************************************Go to the acl group page****************************************************//

WebUI.click(findTestObject('Administration/button_Administration'))

WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))

WebUI.click(findTestObject('Administration/ACL/ACL group creation/p_Access Groups'))

//******************************************************Create a new acl group*****************************************************//

WebUI.click(findTestObject('General/a_Add'))

def file = TestDataFactory.findTestData('ACL group')

WebUI.setText(findTestObject('Administration/ACL/ACL group creation/input_acl_group_name'),
	config.getValue('TimeIndicator', 1) + file.getValue('ACLGroupName', 1))

WebUI.setText(findTestObject('Administration/ACL/ACL group creation/input_acl_group_alias'), file.getValue('ACLGroupAlias', 1))

//This following file contains the information about the user.
def fileUser = TestDataFactory.findTestData('User config')

WebUI.selectOptionByLabel(findTestObject('Administration/ACL/ACL group creation/select_contact'),
	fileUser.getValue('UserAlias', 1) + '1', true)

WebUI.click(findTestObject('General/input_add'))

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL group
WebUI.waitForPageLoad(2)

WebUI.click(findTestObject('General/button_User profile'))

WebUI.waitForElementVisible(findTestObject('General/span_Sign out'), 3)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
