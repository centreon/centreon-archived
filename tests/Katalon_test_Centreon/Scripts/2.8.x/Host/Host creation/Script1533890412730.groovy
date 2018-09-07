import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

import org.stringtemplate.v4.compiler.STParser.element_return

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

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*****************************************************go to host template page****************************************************//

CustomKeywords.'custom.NavigationConfiguration.accessHostTemplates'()

WebUI.waitForPageLoad(3)

//*******************************************************get id of templates*******************************************************//

//This file contains the name of the host templates that I need to connect to the host
def htFile = TestDataFactory.findTestData('Host template')

//This list will store the id of the host templates
List<String> testObject = new ArrayList<String>()

def search = WebUI.modifyObjectProperty(findTestObject('General/input_Search'), 'name', 'equals', 'searchHT', true)

for (def index : (1..htFile.getRowNumbers())) {
	WebUI.setText(search, htFile.getValue('Host template', index))

	WebUI.click(findTestObject('General/button_Search'))

	WebUI.waitForPageLoad(3)

	//I parameters the test object to focus on the correct host template
    def hostTemplate = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals', 
        htFile.getValue('Host template', index), true, FailureHandling.OPTIONAL)

	//The href of the test object contains the id of the host template
    id = WebUI.getAttribute(hostTemplate, 'href')

    array = id.split('id=')

	testObject.add(array[1])
}

//*********************************************************Go to Host page*********************************************************//

CustomKeywords.'custom.NavigationConfiguration.accessHosts'()

WebUI.waitForPageLoad(3)

//**********************************************************create a host**********************************************************//

//This file contains all the information needed for the host, except the host templates
def hostFile = TestDataFactory.findTestData('Host data')

for(def line : (1..hostFile.getRowNumbers()))
{
	for(def hostNumber : ('1'..hostFile.getValue('Number', line)))
	{
		WebUI.click(findTestObject('General/a_Add'))
		
		WebUI.setText(findTestObject('Configuration/Host/Host creation/input_host_name'),
			config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', line) + hostNumber)
		
		WebUI.setText(findTestObject('Configuration/Host/Host creation/input_host_alias'),
			hostFile.getValue('hostAlias', line) + hostNumber)
		
		WebUI.setText(findTestObject('Configuration/Host/Host creation/input_host_address'),
			hostFile.getValue('hostAddress', line))
		
		WebUI.setText(findTestObject('Configuration/Host/Host creation/input_host_snmp_community'),
			hostFile.getValue('SNMPComm', line))
		
		WebUI.selectOptionByValue(findTestObject('Configuration/Host/Host creation/select_12c3'),
			hostFile.getValue('SNMPVersion', line), true)
		
		WebUI.click(findTestObject('Configuration/Host/Host creation/span_Timezone'))
		
		WebUI.setText(findTestObject('Configuration/Host/Host creation/input_Timezone'), hostFile.getValue('timezone', 1))
		
		element = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Host creation/div_Timezone'),
			'title', 'equals', hostFile.getValue('timezone', 1), true)
		
		WebUI.click(element)
		
		//This go through the list and add the host templates one by one
		for (def index : (0..testObject.size() - 1)) {
			//This configure the test object to match the correct host template according to its id
			hostTemplate = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Host creation/select_App-DB-MySQLApp-DB-MySQ'),
				'id', 'equals', 'tpSelect_' + (index), true)
			
			//Add a new template's entry
			WebUI.click(findTestObject('Configuration/Host/Host creation/span_ Add a new entry'))
		
			//Add the new host template
			WebUI.selectOptionByValue(hostTemplate, testObject[index],
				true)
		}
		
		WebUI.click(findTestObject('General/input_submitA'))
		
		//Wait to be sure Edge correctly create the new host
		WebUI.delay(1)
	}
}

//******************************************************Verify element created*****************************************************//

CustomKeywords.'custom.creationVerified.verifyObjectCreated'('searchH',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1')

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
