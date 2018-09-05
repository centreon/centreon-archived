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

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//********************************************************Go to Graphs page********************************************************//

CustomKeywords.'custom.NavigationMonitoring.accessGraphs'()

WebUI.waitForPageLoad(3)

//********************************************************Configure a graph********************************************************//

WebUI.click(findTestObject('Monitoring/Performances/button_Dismiss'))

WebUI.click(findTestObject('Monitoring/Performances/ul_Filter by Host'))

def hostFile = TestDataFactory.findTestData('Host data')

def element = WebUI.modifyObjectProperty(findTestObject('General/div'), 'title', 'equals', 
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1', true)

WebUI.click(element)

WebUI.click(findTestObject('Monitoring/Performances/span_Chart'))

def servicesFile = TestDataFactory.findTestData('Services')

element = WebUI.modifyObjectProperty(element, 'title', 'equals', 
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1 - ' + servicesFile.getValue(1, 5), true)

WebUI.click(element)

WebUI.delay(1)

//**************************************************Check the effect of the eraser*************************************************//

WebUI.click(findTestObject('Monitoring/Performances/img_Rubber'))

WebUI.verifyElementNotPresent(findTestObject('Monitoring/Performances/div_Legend'), 2)

WebUI.closeBrowser()
