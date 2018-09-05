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
import org.junit.After
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
def radio = findTestObject('Administration/ACL/ACL menu/ACL menu creation/radio')

//This file contains the data used to configure the Administration's part of the ACL menu
def fileAdministration = TestDataFactory.findTestData('ACL menu config/ACL menu Administration')

//'0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'

//If 'Administration' == '1' then the access to some or all the pages is granted and I need to see more precisely
//the rights of the ACL menu. If 'Administration' == '0', no access is granted and the job here is done.
if (fileAdministration.getValue('Administration', 1) == '1'){
	select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4', true)
	
	WebUI.click(select)
	
	//The img is the cross on the left.
	img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_4', true)
	
	WebUI.click(img)
	
	//This configure the Parameters page
	if(fileAdministration.getValue('Admin/Parameters', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_4_0', true)
		
		WebUI.click(img)
	
		WebUI.scrollToElement(select, 3)
		
		if(fileAdministration.getValue('Para/Centreon UI', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_0', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/Monitoring', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_1', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/CentCore', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_2', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/My Account', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_3', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/LDAP', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_4', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/RRDTool', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_5', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/Debug', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_6', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/Knowledge Base', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_7', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/CSS', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_8', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/Backup', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_9', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/Options', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_10', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/Data', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_11', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Para/Images', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0_12', true)
			
			WebUI.click(select)
		}
	} 	else{	//When 'Administration' is checked then every select depending on 'Administration' is automatically checked.
				//So if the access to 'Parameters' is not allowed I need to deselect it.
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_0', true)
		
		WebUI.click(select)
	}
	
	//This configure the Extensions page
	if(fileAdministration.getValue('Admin/Extensions', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_4_1', true)
		
		WebUI.click(img)
		
		WebUI.scrollToElement(img, 3)
		
		if(fileAdministration.getValue('Ext/Modules', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_1_0', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('Ext/Widgets', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_1_1', true)
			
			WebUI.click(select)
		}
	} else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_1', true)
		
		WebUI.click(select)
		
		WebUI.scrollToElement(select, 3)
	}
	
	//This configure the ACL page
	if(fileAdministration.getValue('Admin/ACL', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_4_2', true)
		
		WebUI.click(img)
	
		WebUI.scrollToElement(select, 3)
		
		if(fileAdministration.getValue('ACL/Access Groups', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_2_0', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('ACL/Menus Access', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_2_1', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('ACL/Resources Access', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_2_2', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('ACL/Actions Access', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_2_3', true)
			
			WebUI.click(select)
		}
		if(fileAdministration.getValue('ACL/Reload ACL', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_2_4', true)
			
			WebUI.click(select)
		}
	} else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_2', true)
		
		WebUI.click(select)
	
		WebUI.scrollToElement(select, 3)
	}
	
	//This configure the Logs page
	if(fileAdministration.getValue('Admin/Logs', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_4_3', true)
		
		WebUI.click(img)
		
		if(fileAdministration.getValue('Logs/Visualisation', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_3_0', true)
			
			WebUI.click(select)
		}
	} else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_3', true)
		
		WebUI.click(select)
	}
	
	//This configure the Sessions page. As their is no sub-page, I only verify if I need to deselect it.
	if(fileAdministration.getValue('Admin/Sessions', 1) == '0'){
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_4', true)
		
		WebUI.click(select)
	}
	
	//This configure the Server Status page
	if(fileAdministration.getValue('Admin/Server Status', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_4_5', true)
		
		WebUI.click(img)
		
		if(fileAdministration.getValue('Server/Databases', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_5_0', true)
			
			WebUI.click(select)
		}
	} else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_5', true)
		WebUI.click(select)
	}
	
	//This configure the About page
	if(fileAdministration.getValue('Admin/About', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_4_6', true)
		
		WebUI.click(img)
		
		if(fileAdministration.getValue('About/About', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_6_0', true)
			
			WebUI.click(select)
		}
	} else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i4_6', true)
		
		WebUI.click(select)
	}
}

WebUI.click(findTestObject('General/input_submitC'))

//Wait to be sure Edge correctly modify the ACL menu
WebUI.waitForPageLoad(1)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
