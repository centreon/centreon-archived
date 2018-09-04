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
import java.io.FileNotFoundException as FileNotFoundException
import java.io.IOException as IOException
import java.util.Date as Date
import org.apache.poi.xssf.usermodel.XSSFCell as XSSFCell
import org.apache.poi.xssf.usermodel.XSSFRow as XSSFRow
import org.apache.poi.xssf.usermodel.XSSFSheet as XSSFSheet
import org.apache.poi.xssf.usermodel.XSSFWorkbook as XSSFWorkbook
import java.lang.String as String
import org.junit.After as After

//This file contains the url and the admin's login and password
def config = TestDataFactory.findTestData('Configuration')

def user = TestDataFactory.findTestData('user config')

WebUI.openBrowser(config.getValue('url', 1))

//The loggin page will be Home/Custom Views so if the access is not granted, I need to modify it.
def home = TestDataFactory.findTestData('ACL menu config/ACL menu Home')

def element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu creation/select_Home'),
	'id', 'equals', 'i0_0', true)

def aclMenuFile = TestDataFactory.findTestData('ACL menu config/ACL menu')

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals',
	config.getValue('TimeIndicator', 1) + aclMenuFile.getValue('ACLMenuName', 1), true)

if (home.getValue('Home/Custom Views', 1) == '0') {
	
    //********************************************************Log in as an admin*******************************************************//
	
    //I need to be logged as a admin to be sure to be able to modify the ACL menu
    WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

    WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

    WebUI.click(findTestObject('General/Login/input_submitLogin'))

    //********************************************************go to ACL menu*******************************************************//
	
	WebUI.click(findTestObject('Old menu/Administration/a_Administration'))
	
    WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

    WebUI.click(findTestObject('Old menu/Administration/a_Menus Access'))

    //****************************************************Configure the ACL menu***************************************************//
	
    WebUI.click(a)
	
	WebUI.waitForElementVisible(findTestObject('Home/create view/img_0'), 3)

    //This grant access to 'Add view' to allow the function to create a view for the tests
    //The img is the cross on the left on the ACL menu page
    WebUI.click(findTestObject('Home/create view/img_0'))
	
	WebUI.waitForElementVisible(element, 3)
	
	WebUI.click(element)

    WebUI.click(findTestObject('General/input_submitC'))
	
    WebUI.click(findTestObject('Old menu/a_Logout'))
}

//************************************************************Autologin************************************************************//

//Make sure Edge is waiting for the logout.
WebUI.waitForPageLoad(3)

WebUI.navigateToUrl(config.getValue('url', 1) + 'index.php?autologin=1&useralias=' + config.getValue('TimeIndicator', 1) + 
	user.getValue('UserName', 1) + '1' + '&token=' + user.getValue('autologin', 1) + '&p=103&min=1')

WebUI.delay(1)

WebUI.verifyElementPresent(findTestObject('Home/h4_No view'), 3)

WebUI.verifyElementNotPresent(findTestObject('Old menu/a_Logout'), 3)

WebUI.verifyElementNotPresent(findTestObject('Home/button_Home'), 3)

WebUI.closeBrowser()

if (home.getValue('Home/Custom Views', 1) == '0') {
	
    //********************************************************Log in as an admin*******************************************************//
    
	WebUI.openBrowser(config.getValue('url', 1))

    //I need to be logged as a admin to be sure to be able to modify the ACL menu
    WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

    WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

    WebUI.click(findTestObject('General/Login/input_submitLogin'))

    //********************************************************go to ACL menu*******************************************************//

	WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

    WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

    WebUI.click(findTestObject('Old menu/Administration/a_Menus Access'))

    //****************************************************Configure the ACL menu***************************************************//
	
    WebUI.click(a)
	
	//This is to avoid Chrome's failure
	WebUI.delay(1)

    //This grant access to 'Add view' to allow the function to create a view for the tests
    //The img is the cross on the left on the ACL menu page
    WebUI.click(findTestObject('Home/create view/img_0'))
	
	WebUI.waitForElementVisible(element, 3)

    WebUI.click(element)

    WebUI.click(findTestObject('General/input_submitC'))

    //Wait to be sure that the modification is done correctly
    WebUI.waitForPageLoad(3)

    WebUI.click(findTestObject('Old menu/a_Logout'))

    WebUI.closeBrowser()
}
