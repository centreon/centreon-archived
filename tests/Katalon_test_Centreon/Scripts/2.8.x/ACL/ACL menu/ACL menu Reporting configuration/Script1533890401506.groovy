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

//*******************************************************Go to ACL menu page*******************************************************//

CustomKeywords.'custom.NavigationAdministration.accessMenusAccess'()

WebUI.waitForPageLoad(3)

//******************************************************Configure the ACL menu*****************************************************//

def file = TestDataFactory.findTestData('ACL menu config/ACL menu')

WebUI.setText(findTestObject('Administration/ACL/ACL menu/input_Search'),
	file.getValue('ACLMenuName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.delay(1)

def a = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'text', 'equals', ACLMenuName, true)

WebUI.click(a)

WebUI.delay(1)

//These 3 followings elements are used to interact with the images, selects and radios. I modify directly their id to avoid
//storing 100 (or more) different items.
def select = findTestObject('Administration/ACL/ACL menu/ACL menu creation/select_Home')
def img = findTestObject('Administration/ACL/ACL menu/ACL menu creation/img_Home')

//This file contains the data used to configure the Reporting's part of the ACL menu
def fileReporting = TestDataFactory.findTestData('ACL menu config/ACL menu Reporting')

//If 'Reporting' == '1' then the access to some or all the pages is granted and I need to see more precisely
//the rights of the ACL menu. If 'Reporting' == '0', no access is granted and the job here is done.
if (fileReporting.getValue('Reporting', 1) == '1'){
	select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i2', true)
	
	WebUI.click(select)
	
	WebUI.scrollToElement(select, 3)
	
	//img is the cross on the left.
	img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_2', true)
	
	WebUI.click(img)
	
	//This configure the Dashboard page
	if(fileReporting.getValue('Rep/Dashboard', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_2_0', true)
		
		WebUI.click(img)
		
		if(fileReporting.getValue('Dash/Hosts', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i2_0_0', true)
			
			WebUI.click(select)
		}
		if(fileReporting.getValue('Dash/Services', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i2_0_1', true)
			
			WebUI.click(select)
		}
		if(fileReporting.getValue('Dash/hg', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i2_0_2', true)
			
			WebUI.click(select)
		}
		if(fileReporting.getValue('Dash/sg', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i2_0_3', true)
			
			WebUI.click(select)
		}
	} else{		//When 'Reporting' is checked then every select depending on 'Reporting' is automatically checked.
				//So if the access to 'Dashboard' is not allowed I need to deselect it.
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i2_0', true)
		
		WebUI.click(select)
	}
}

WebUI.click(findTestObject('General/input_submitC'))

//Wait to be sure Edge correctly modify the ACL menu
WebUI.waitForPageLoad(1)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
