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
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.Date;
import org.apache.poi.xssf.usermodel.XSSFCell;
import org.apache.poi.xssf.usermodel.XSSFRow;
import org.apache.poi.xssf.usermodel.XSSFSheet;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
import java.lang.String
import org.junit.After

//This file contains the url and the admin's login and password
def config = TestDataFactory.findTestData('Configuration')

def user = TestDataFactory.findTestData('user config')

WebUI.openBrowser(config.getValue('url', 1))

def aclMenuFile = TestDataFactory.findTestData('ACL menu config/ACL menu')

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals',
	config.getValue('TimeIndicator', 1) + aclMenuFile.getValue('ACLMenuName', 1), true)

//The loggin page will be Reporting Dashboards Hosts page so if the access is not granted, I need to modify it.
def reporting = TestDataFactory.findTestData('ACL menu config/ACL menu Reporting')

if(reporting.getValue('Dash/Hosts', 1) == '0'){
	
	//********************************************************Log in as an admin*******************************************************//
	
	//I need to be logged as a admin to be sure to be able to modify the ACL menu
	WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
	WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
	WebUI.click(findTestObject('General/Login/input_submitLogin'))
	
	//********************************************************go to ACL menu*******************************************************//
	
	CustomKeywords.'custom.NavigationAdministration.accessMenusAccess'()
	
	//****************************************************Configure the ACL menu***************************************************//
	
	WebUI.delay(1)
	
	WebUI.click(a)
	
	//This grant access to 'Add view' to allow the function to create a view for the tests
	//The img is the cross on the left on the ACL menu page
	
	def img = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu creation/img_Home'),
		'id', 'equals', 'img_2', true)
	
	//This is to avoid Chrome's failure
	WebUI.delay(1)
	
	WebUI.click(img)
	
	img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_2_0', true)
	
	WebUI.click(img)
	
	def select = WebUI.modifyObjectProperty(
		findTestObject('Administration/ACL/ACL menu/ACL menu creation/select_Home'),
		'id', 'equals', 'i2_0_0', true)
	
	WebUI.click(select)
	
	WebUI.click(findTestObject('General/input_submitC'))
	
	WebUI.click(findTestObject('Old menu/a_Logout'))
}

//************************************************************Autologin************************************************************//

//Make sure Edge is waiting for the logout.
WebUI.waitForPageLoad(3)

WebUI.navigateToUrl(config.getValue('url', 1) + 'index.php?autologin=1&useralias=' + config.getValue('TimeIndicator', 1) +
	 user.getValue('UserName', 1) + '1' + '&token=' + user.getValue('autologin', 1) + '&p=30701&min=1')

WebUI.waitForPageLoad(3)

WebUI.verifyElementPresent(findTestObject('Reporting/Hosts/select host'), 3)

WebUI.verifyElementPresent(findTestObject('Reporting/Hosts/button_Apply period'), 3)

WebUI.verifyElementNotPresent(findTestObject('Old menu/a_Logout'), 3)

WebUI.verifyElementNotPresent(findTestObject('Reporting/Hosts/a_Hosts'), 3)

WebUI.verifyElementNotPresent(findTestObject('Reporting/button_Reporting'), 3)

WebUI.closeBrowser()

if(reporting.getValue('Dash/Hosts', 1) == '0'){
	
	//********************************************************Log in as an admin*******************************************************//

	WebUI.openBrowser(config.getValue('url', 1))
	
	//I need to be logged as a admin to be sure to be able to modify the ACL menu
	WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))
	
	WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
	WebUI.click(findTestObject('General/Login/input_submitLogin'))
	
	//********************************************************go to ACL menu*******************************************************//
	
	CustomKeywords.'custom.NavigationAdministration.accessMenusAccess'()
	
	//****************************************************Configure the ACL menu***************************************************//
		
	WebUI.delay(1)
	
	WebUI.click(a)
	
	//This grant access to 'Add view' to allow the function to create a view for the tests
	//The img is the cross on the left on the ACL menu page
	def img = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu creation/img_Home'),
		'id', 'equals', 'img_2', true)
	
	//This is to avoid Chrome's failure
	WebUI.delay(1)
	
	WebUI.click(img)
	
	img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_2_0', true)
	
	WebUI.click(img)
	
	def select = WebUI.modifyObjectProperty(
		findTestObject('Administration/ACL/ACL menu/ACL menu creation/select_Home'),
		'id', 'equals', 'i2_0_0', true)
	
	WebUI.click(select)
	
	WebUI.click(findTestObject('General/input_submitC'))
	
	//Wait to be sure that the modification is done correctly
	WebUI.waitForPageLoad(3)

	WebUI.click(findTestObject('Old menu/a_Logout'))

	WebUI.closeBrowser()
}
