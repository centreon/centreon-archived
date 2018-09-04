import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

import org.junit.After

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

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), userName)

WebUI.setText(findTestObject('General/Login/input_password'), userPassword)

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//****************************************************Check Administration page****************************************************//

//This file contains the information about Monitoring's part of the acl menu
def file = TestDataFactory.findTestData('ACL menu config/ACL menu Monitoring')

//'0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'


WebUI.waitForPageLoad(3)

//I change the element's properties to select the correct element according to the name displayed
def menu = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'text', 'equals', 'Status Details', true)

def subMenu = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'title', 'equals', 'Services', true)

//If 'Administration' == '1' the access is granted and the link is displayed
if (file.getValue('Monitoring', 1) == '1') {
	WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

	//This checks the Monitoring/Status Details page
    if (file.getValue('Mon/Status Details', 1) == '1') {
		WebUI.click(menu)
		
		//If the access is granted, the button exists and I can click on it.
        if (file.getValue('Stat/Services', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { //The access is not granted, the button is not displayed
            WebUI.verifyElementNotPresent(subMenu, 1)
        }
        
        p = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Hosts', true)
		
        if (file.getValue('Stat/Hosts', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        p = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Services Grid', true)

        if (file.getValue('Stat/Services Grid', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        p = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Services by Hostgroup', true)

        if (file.getValue('Stat/Services by hg', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        p = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Services by Servicegroup', true)
		
        if (file.getValue('Stat/Services by sg', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        p = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Hostgroups Summary', true)
		
        if (file.getValue('Stat/hg Summary', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Performances', true)
	
	//This check the Monitoring/Performance page
    if (file.getValue('Mon/Performance', 1) == '1') {
		WebUI.click(menu)
		
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Graphs', true)
		
        if (file.getValue('Perf/Graphs', 1) == '1') {
            WebUI.click(subMenu)

            WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_Dismiss'), FailureHandling.OPTIONAL)

            WebUI.click(findTestObject('Monitoring/Performances/ul_Filter by Host'))

			def hostFile = TestDataFactory.findTestData('Host data')
			
			def element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Host'),
				'title', 'equals', config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1', true)
			
			WebUI.click(element)

            WebUI.click(findTestObject('Monitoring/Performances/span_Chart'))
			
			def servicesFile = TestDataFactory.findTestData('Services')
			
			element = WebUI.modifyObjectProperty(
				findTestObject('Administration/ACL/ACL menu/ACL menu check/div_host - service'), 'title', 'equals',
				config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1 - ' + servicesFile.getValue(1, 2), true)
			
            WebUI.click(element)

            if (file.getValue('Perf/Chart split', 1) == '1') {
                WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Split chart'))

                WebUI.click(subMenu)

                WebUI.click(findTestObject('Monitoring/Performances/ul_Filter by Host'))
				
                element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Host'),
					'title', 'equals', config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1', true)

				WebUI.click(element)
				
                WebUI.click(findTestObject('Monitoring/Performances/span_Chart'))

                element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_host - service'),
					'title', 'equals', config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1 - ' +
					servicesFile.getValue(1, 2), true)
				
				WebUI.click(element)
            } else {
                WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Split chart'), 1)
            }

            if (file.getValue('Perf/Chart periods', 1) == '1') {
                WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Display multiple periods'))
            } else {
                WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Display multiple periods'), 
                    1)
            }
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }

        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Templates', true)
		
        if (file.getValue('Perf/Templates', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }

        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Curves', true)

        if (file.getValue('Perf/Curves', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }

        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Metrics', true)

        if (file.getValue('Perf/Metrics', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Downtimes', true)
	
	//This check the Monitoring/Downtimes page
    if (file.getValue('Mon/Downtimes', 1) == '1') {
		WebUI.click(menu)
		
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Downtimes', true)

        if (file.getValue('Down/Downtimes', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Recurrent downtimes', true)

        if (file.getValue('Down/Recurrent downtimes', 1) == '1') {	//Read and write
            WebUI.click(subMenu)

            WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 1)
        } else if (file.getValue('Down/Recurrent downtimes', 1) == '2') {	//Read only
            WebUI.click(subMenu)

            WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 1) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else {	//Not access
            WebUI.verifyElementNotPresent(subMenu, 1)
        }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Comments', true)

        if (file.getValue('Down/Comments', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Event Logs', true)
	
	//This check the Monitoring/Event Logs page
    if (file.getValue('Mon/Event Logs', 1) == '1') {
		WebUI.click(menu)
		
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Event Logs', true)

        if (file.getValue('Event/Event Logs', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'System Logs', true)

        if (file.getValue('Event/System Logs', 1) == '1') {
            WebUI.click(subMenu) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
} else { WebUI.verifyElementNotPresent(findTestObject('Old menu/Monitoring/a_Monitoring'), 1) }

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
