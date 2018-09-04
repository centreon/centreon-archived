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

//I change the element's properties to select the correct element according to the name displayed
def span = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/span'),
	'text', 'equals', 'Status Details', true)

def p = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/p'),
	'text', 'equals', 'Services', true)

//If 'Administration' == '1' the access is granted and the link is displayed
if (file.getValue('Monitoring', 1) == '1') {
	WebUI.click(findTestObject('Monitoring/button_Monitoring'))

	WebUI.waitForPageLoad(3)

	//This checks the Monitoring/Status Details page
    if (file.getValue('Mon/Status Details', 1) == '1') {
		WebUI.mouseOver(span)
		
		//If the access is granted, the button exists and I can click on it.
        if (file.getValue('Stat/Services', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { //The access is not granted, the button is not displayed
            WebUI.verifyElementNotPresent(p, 1)
        }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Hosts', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Stat/Hosts', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Services Grid', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Stat/Services Grid', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Services by Hostgroup', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Stat/Services by hg', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Services by Servicegroup', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Stat/Services by sg', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Hostgroups Summary', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Stat/hg Summary', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Performances', true)
	
	//This check the Monitoring/Performance page
    if (file.getValue('Mon/Performance', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Graphs', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Perf/Graphs', 1) == '1') {
            WebUI.click(p)

            WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_Dismiss'), FailureHandling.OPTIONAL)

            WebUI.click(findTestObject('Monitoring/Performances/ul_Filter by Host'))

            WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Host'))

            WebUI.click(findTestObject('Monitoring/Performances/span_Chart'))

			def hostFile = TestDataFactory.findTestData('Host data')
			
			def servicesFile = TestDataFactory.findTestData('Services')
			
			def element = WebUI.modifyObjectProperty(
				findTestObject('Administration/ACL/ACL menu/ACL menu check/div_host - service'), 'title', 'equals',
				config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1 - ' + servicesFile.getValue(1, 2), true)
			
            WebUI.click(element)

            if (file.getValue('Perf/Chart split', 1) == '1') {
                WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Split chart'))

                WebUI.click(p)

                WebUI.click(findTestObject('Monitoring/Performances/ul_Filter by Host'))

                WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Host'))

                WebUI.click(findTestObject('Monitoring/Performances/span_Chart'))

                WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_host - service'))
            } else {
                WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Split chart'), 1)
            }

            if (file.getValue('Perf/Chart periods', 1) == '1') {
                WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Display multiple periods'))
            } else {
                WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/a_Display multiple periods'), 
                    1)
            }
        } else { WebUI.verifyElementNotPresent(p, 1) }

        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Templates', true)
		
		WebUI.mouseOver(span)
		
        if (file.getValue('Perf/Templates', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }

        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Curves', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Perf/Curves', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }

        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Metrics', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Perf/Metrics', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Downtimes', true)
	
	//This check the Monitoring/Downtimes page
    if (file.getValue('Mon/Downtimes', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Downtimes', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Down/Downtimes', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Recurrent downtimes', true)

        if (file.getValue('Down/Recurrent downtimes', 1) == '1') {	//Read and write
            WebUI.click(p)

            WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 1)
        } else if (file.getValue('Down/Recurrent downtimes', 1) == '2') {	//Read only
            WebUI.click(p)

            WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 1) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else {	//Not access
            WebUI.verifyElementNotPresent(p, 1)
        }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Comments', true)

        if (file.getValue('Down/Comments', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Event Logs', true)
	
	//This check the Monitoring/Event Logs page
    if (file.getValue('Mon/Event Logs', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Event Logs', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Event/Event Logs', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'System Logs', true)

        if (file.getValue('Event/System Logs', 1) == '1') {
            WebUI.click(p) 
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
} else { WebUI.verifyElementNotPresent(findTestObject('Monitoring/button_Monitoring'), 1) }

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
