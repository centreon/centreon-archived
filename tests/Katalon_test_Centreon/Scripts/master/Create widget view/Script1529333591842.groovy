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

def aclMenuFile = TestDataFactory.findTestData('ACL menu')

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals',
	config.getValue('TimeIndicator', 1) + aclMenuFile.getValue('ACLMenuName', 1), true)

if(addWidget == '0'){
	//********************************************************Login as admin*******************************************************//
	//I need to be logged as a admin to be sure to be able to modify the ACL menu
	WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
	WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
	WebUI.click(findTestObject('General/Login/input_submitLogin'))
	
	//********************************************************go to ACL menu*******************************************************//

	WebUI.click(findTestObject('Administration/button_Administration'))
	
	WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))
	
	WebUI.click(findTestObject('Administration/ACL/ACL menu/p_Menus Access'))

	//****************************************************Configure the ACL menu***************************************************//
	
	WebUI.click(a)
	
	//This grant access to 'Add Widget' to allow the function to create a view for the tests
	//The img is the cross on the left on the ACL menu page
	WebUI.click(findTestObject('Home/create view/img_0'))
	
	WebUI.click(findTestObject('Home/create view/img_0_0'))
	
	WebUI.click(findTestObject('Home/Create widget view/input_acl_r_topos199'))
	
	WebUI.click(findTestObject('General/input_submitC'))
	
	//Wait to be sure Edge correctly modify the ACL menu
	WebUI.waitForPageLoad(3)
	
	WebUI.click(findTestObject('General/button_User profile'))
	
	WebUI.delay(1)
	
	WebUI.click(findTestObject('General/span_Sign out'))
}

//*******************************************************Login as simple user******************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), userName)
	
WebUI.setText(findTestObject('General/Login/input_password'), userPassword)
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

//********************************************************go to Custom Views*******************************************************//

WebUI.click(findTestObject('Home/button_Home'))

//Click on the button on the right of the Custom Views page that allows you to modify, create, delete... a view
WebUI.click(findTestObject('Home/Create widget view/span_Custom Views'))

WebUI.waitForElementClickable(findTestObject('Home/Create widget view/img_ico-14'), 3)

WebUI.click(findTestObject('Home/Create widget view/img_ico-14'))

//This is to avoid Chrome's failure
WebUI.delay(1)

//This create a widget
WebUI.click(findTestObject('Home/Create widget view/button_Add widget'))

WebUI.click(findTestObject('Home/Create widget view/span_Widget'))

WebUI.click(findTestObject('Home/Create widget view/div_Engine-status'))

WebUI.setText(findTestObject('Home/Create widget view/input_widget_title'), 'Katalon_widget')

WebUI.waitForElementClickable(findTestObject('Home/Create widget view/input_submit'), 1)

WebUI.click(findTestObject('Home/Create widget view/input_submit'))

WebUI.click(findTestObject('General/button_User profile'))

WebUI.delay(1)
	
WebUI.click(findTestObject('General/span_Sign out'))

if(addWidget == '0'){
	
	//********************************************************Login as admin*******************************************************//
	
	WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
	WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
	WebUI.click(findTestObject('General/Login/input_submitLogin'))
	
	//********************************************************go to ACL menu*******************************************************//
	
	WebUI.click(findTestObject('Administration/button_Administration'))
	
	WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))
	
	WebUI.click(findTestObject('Administration/ACL/ACL menu/p_Menus Access'))

	//****************************************************Configure the ACL menu***************************************************//
	
	WebUI.click(a)
	
	//This revoke the access to 'Add View' now that the view is created
	//The img is the cross on the left on the ACL menu page
	WebUI.click(findTestObject('Home/create view/img_0'))
	
	WebUI.click(findTestObject('Home/create view/img_0_0'))
	
	WebUI.click(findTestObject('Home/Create widget view/input_acl_r_topos199'))
	
	WebUI.click(findTestObject('General/input_submitC'))

	//Wait to be sure Edge correctly modify the ACL menu
	WebUI.waitForPageLoad(3)
	
	WebUI.click(findTestObject('General/button_User profile'))
	
	WebUI.delay(1)
	
	WebUI.click(findTestObject('General/span_Sign out'))
}

WebUI.closeBrowser()
