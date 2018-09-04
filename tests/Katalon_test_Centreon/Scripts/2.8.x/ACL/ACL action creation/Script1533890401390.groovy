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

//*********************************************************go to ACL action********************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

WebUI.click(findTestObject('Old menu/Administration/a_Actions Access'))

WebUI.waitForPageLoad(3)

//*****************************************************create a new ACL action*****************************************************//

WebUI.click(findTestObject('General/a_Add'))

def file = TestDataFactory.findTestData('ACL action')
//This file only contains 1 column. The first line of this file contains the name of the acl action, the second line is its alias.
//The following lines contains the name of the boxes of the actions this code needs to check

WebUI.setText(findTestObject('Administration/ACL/ACL action creation/input_acl_action_name'),
	config.getValue('TimeIndicator', 1) + file.getValue(1, 1))

WebUI.setText(findTestObject('Administration/ACL/ACL action creation/input_acl_action_description'), file.getValue(1, 2))

def box = findTestObject('Administration/ACL/ACL action creation/input_action')
//This object is used to click on the action's checkbox. I juste change one property: it's name to check the correct box.

for(int i = 3; i < file.getRowNumbers(); i++){		//While there is another line, I check a new box
	box = WebUI.modifyObjectProperty(box, 'name', 'equals', file.getValue(1, i), true)
	
	WebUI.click(box)
}

def aclGroupFile = TestDataFactory.findTestData('ACL group')

def option = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL action creation/option_Katalon_acl_group'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + aclGroupFile.getValue('ACLGroupName', 1), true)

//Select then add the acl group
WebUI.click(option)

WebUI.click(findTestObject('Administration/ACL/ACL action creation/add_acl_group'))

WebUI.click(findTestObject('Administration/ACL/ACL action creation/input_action'))

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL action
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
