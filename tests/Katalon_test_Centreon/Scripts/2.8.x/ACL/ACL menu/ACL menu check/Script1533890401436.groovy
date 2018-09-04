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
import groovy.swing.factory.TDFactory
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

def user = TestDataFactory.findTestData('user config')

def config = TestDataFactory.findTestData('Configuration')

//*****************************************************************Home*****************************************************************//

//I store the data about the acl menu Home page in 'home'
def home = TestDataFactory.findTestData('ACL menu config/ACL menu Home')

//If I have access to the widget parameters, I create a view and a widget view to test the acl menu about the Home/Custom Views page.
if(home.getValue('Home', 1) == '1' && home.getValue('Cust/Widget Parameters', 1) == '1'){
	WebUI.callTestCase(findTestCase('2.8.x/Create view'), 
		['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
		'userPassword' : user.getValue('password', 1), 'addView' : home.getValue('Cust/Add View', 1)])

	WebUI.callTestCase(findTestCase('2.8.x/Create widget view'),
		['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
		'userPassword' : user.getValue('password', 1), 'addWidget' : home.getValue('Cust/Add Widget', 1)])
}

//I launch the acl menu check on Home page
WebUI.callTestCase(findTestCase('2.8.x/ACL/ACL menu/ACL menu Home check'),
	['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
	'userPassword' : user.getValue('password', 1)])

//**************************************************************Monitoring**************************************************************//

def monitoring = TestDataFactory.findTestData('ACL menu config/ACL menu Monitoring')

//I launch the acl menu check on Monitoring page
WebUI.callTestCase(findTestCase('2.8.x/ACL/ACL menu/ACL menu Monitoring check'),
	['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
	'userPassword' : user.getValue('password', 1)])

//**************************************************************Reporting***************************************************************//

def reporting = TestDataFactory.findTestData('ACL menu config/ACL menu Reporting')

//I launch the acl menu check on Reporting page
WebUI.callTestCase(findTestCase('2.8.x/ACL/ACL menu/ACL menu Reporting check'),
	['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
	'userPassword' : user.getValue('password', 1)])

//************************************************************Configuration*************************************************************//

def configuration = TestDataFactory.findTestData('ACL menu config/ACL menu Configuration')

//I launch the acl menu check on Configuration page
WebUI.callTestCase(findTestCase('2.8.x/ACL/ACL menu/ACL menu Configuration check'),
	['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
	'userPassword' : user.getValue('password', 1)])

WebUI.callTestCase(findTestCase('2.8.x/ACL/ACL menu/ACL menu Configuration check part2'),
	['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
	'userPassword' : user.getValue('password', 1)])

//************************************************************Administration************************************************************//

def administration = TestDataFactory.findTestData('ACL menu config/ACL menu Administration')

//I launch the acl menu check on Administration page
WebUI.callTestCase(findTestCase('2.8.x/ACL/ACL menu/ACL menu Administration check'),
	['userName' : config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1) + '1',
	'userPassword' : user.getValue('password', 1)])

//If no page is allowed, then the user has access to nothing
if(home.getValue('Home', 1) == '0' && monitoring.getValue('Monitoring', 1) == '0' && reporting.getValue('Reporting', 1) == '0'
	&& configuration.getValue('Configuration', 1) == '0' && administration.getValue('Administration', 1) == '0'){	
	WebUI.openBrowser(config.getValue('url', 1))
	
	//***************************************************************Login**************************************************************//
	
	WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('TimeIndicator', 1) + user.getValue('UserName', 1))
	
	WebUI.setText(findTestObject('General/Login/input_password'), user.getValue('password', 1))
	
	WebUI.click(findTestObject('General/Login/input_submitLogin'))

	//*****************************************************Verify what is displayed*****************************************************//
	
	WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 1)
	
	WebUI.click(findTestObject('Old menu/a_Logout'))
	
	WebUI.closeBrowser()
}
