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
	
def aclMenuFile = TestDataFactory.findTestData('ACL menu config/ACL menu')
	
def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals',
		config.getValue('TimeIndicator', 1) + aclMenuFile.getValue('ACLMenuName', 1), true)

if(addView == '0'){
	
	//********************************************************Login as admin*******************************************************//
	
	//I need to be logged as a admin to be sure to be able to modify the ACL menu
	WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
	WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
	WebUI.click(findTestObject('General/Login/input_submitLogin'))
	
	//********************************************************go to ACL menu*******************************************************//
	
	CustomKeywords.'custom.NavigationAdministration.accessMenusAccess'()

	//****************************************************Configure the ACL menu***************************************************//
	
	WebUI.delay(1)
	
	WebUI.click(a)
	
	WebUI.delay(1)
	
	//This grant access to 'Add view' to allow the function to create a view for the tests
	//The img is the cross on the left on the ACL menu page
	WebUI.click(findTestObject('Home/create view/img_0'))
	
	WebUI.click(findTestObject('Home/create view/img_0_0'))
	
	WebUI.click(findTestObject('Home/create view/input_acl_r_topos219'))
	
	WebUI.click(findTestObject('General/input_submitC'))
	
	//Wait to be sure Edge correctly modify the ACL menu
	WebUI.waitForPageLoad(3)
	
	WebUI.click(findTestObject('Old menu/a_Logout'))
}

//*******************************************************Login as simple user******************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), userName)
	
WebUI.setText(findTestObject('General/Login/input_password'), userPassword)
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

//********************************************************go to Custom Views*******************************************************//

CustomKeywords.'custom.NavigationHome.accessCustomViews'()

//**********************************************************create a View**********************************************************//

//Click on the button on the right of the Custom Views page that allows you to modify, create, delete... a view
WebUI.click(findTestObject('Home/create view/img_editView'))

WebUI.delay(1)

//This create a view
WebUI.click(findTestObject('Home/create view/button_Add view'))

WebUI.setText(findTestObject('Home/create view/input_name'), 'Katalon_view')

WebUI.click(findTestObject('Home/create view/input_layoutlayout'))

WebUI.click(findTestObject('Home/create view/input_submit'))

WebUI.click(findTestObject('Old menu/a_Logout'))

if(addView == '0'){
	
	//********************************************************Login as admin*******************************************************//
	
	WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
	WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
	WebUI.click(findTestObject('General/Login/input_submitLogin'))
	
	//********************************************************go to ACL menu*******************************************************//

	CustomKeywords.'custom.NavigationAdministration.accessMenusAccess'()	

	//****************************************************Configure the ACL menu***************************************************//
	
	WebUI.delay(1)
	
	WebUI.click(a)
	
	WebUI.delay(1)
	
	//This revoke the access to 'Add View' now that the view is created
	//The img is the cross on the left on the ACL menu page
	WebUI.click(findTestObject('Home/create view/img_0'))
	
	WebUI.click(findTestObject('Home/create view/img_0_0'))
	
	WebUI.click(findTestObject('Home/create view/input_acl_r_topos219'))
	
	WebUI.click(findTestObject('General/input_submitC'))

	//Wait to be sure Edge correctly modify the ACL menu
	WebUI.waitForPageLoad(3)
	
	WebUI.click(findTestObject('Old menu/a_Logout'))
}

WebUI.closeBrowser()
