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

WebUI.click(findTestObject('Administration/button_Administration'))

WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))

WebUI.click(findTestObject('Administration/ACL/ACL menu/p_Menus Access'))

//******************************************************Configure the ACL menu*****************************************************//

WebUI.delay(1)

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
def fileMonitoring = TestDataFactory.findTestData('ACL menu config/ACL menu Monitoring')

//'0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'

//If 'Monitoring' == '1' then the access to some or all the pages is granted and I need to see more precisely
//the rights of the ACL menu. If 'Monitoring' == '0', no access is granted and the job here is done.
if (fileMonitoring.getValue('Monitoring', 1) == '1'){
	select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1', true)

	WebUI.click(select)
	
	WebUI.scrollToElement(select, 3)
	
	//img is the cross on the left.
	img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_1', true)
	
	WebUI.click(img)
	
	//This configure the Status Details page
	if(fileMonitoring.getValue('Mon/Status Details', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_1_0', true)
		
		WebUI.click(img)
		
		if(fileMonitoring.getValue('Stat/Services', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_0_0', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Stat/Hosts', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_0_1', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Stat/Services Grid', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_0_2', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Stat/Services by hg', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_0_3', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Stat/Services by sg', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_0_4', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Stat/hg Summary', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_0_5', true)
			
			WebUI.click(select)
		}
	}
	else{	//When 'Configuration' is checked then every select depending on 'Configuration' is automatically checked.
			//So if the access to 'Hosts' is not allowed I need to deselect it.
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_0', true)
		
		WebUI.click(select)
	}
	
	//This configure the Performances page
	if(fileMonitoring.getValue('Mon/Performance', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_1_1', true)
		
		WebUI.click(img)
		
		WebUI.scrollToElement(img, 3)
		
		if(fileMonitoring.getValue('Perf/Graphs', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_1_0', true)
			
			WebUI.click(select)
		}
		else{
			img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_1_1_0', true)
			
			WebUI.click(img)
			
			if(fileMonitoring.getValue('Perf/Chart split', 1) == '0'){
				select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_1_0_0', true)
				
				WebUI.click(select)
			}
			if(fileMonitoring.getValue('Perf/Chart periods', 1) == '0'){
				select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_1_0_1', true)
				
				WebUI.click(select)
			}
		}
		if(fileMonitoring.getValue('Perf/Templates', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_1_1', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Perf/Curves', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_1_2', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Perf/Metrics', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_1_3', true)
			
			WebUI.click(select)
		}
	}
	else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_1', true)
		
		WebUI.click(select)
		
		WebUI.scrollToElement(select, 3)
	}
	
	//This configure the Event Logs page
	if(fileMonitoring.getValue('Mon/Event Logs', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_1_2', true)
		
		WebUI.click(img)
		
		if(fileMonitoring.getValue('Event/Event Logs', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_2_0', true)
			
			WebUI.click(select)
		}
		if(fileMonitoring.getValue('Event/System Logs', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_2_1', true)
			
			WebUI.click(select)
		}
	}
	else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_2', true)
		
		WebUI.click(select)
	}
	
	//This configure the Downtimes page
	if(fileMonitoring.getValue('Mon/Downtimes', 1) == '1'){
		img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_1_3', true)
		
		WebUI.click(img)
		
		if(fileMonitoring.getValue('Down/Downtimes', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_3_0', true)
			
			WebUI.click(select)
		}
		radio = WebUI.modifyObjectProperty(radio, 'value', 'equals',
			fileMonitoring.getValue('Down/Recurrent downtimes', 1), true)
		
		WebUI.click(radio)
		
		if(fileMonitoring.getValue('Down/Comments', 1) == '0'){
			select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_3_2', true)
			
			WebUI.click(select)
		}
	}
	else{
		select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i1_3', true)
		
		WebUI.click(select)
	}
}

WebUI.click(findTestObject('General/input_submitC'))

//Wait to be sure Edge correctly modify the ACL menu
WebUI.waitForPageLoad(2)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
