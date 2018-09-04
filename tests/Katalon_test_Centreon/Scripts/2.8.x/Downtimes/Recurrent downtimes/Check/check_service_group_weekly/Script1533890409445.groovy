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

//********************************************************Login as an admin********************************************************//

WebUI.setText(findTestObject('Monitoring/Downtimes/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('Monitoring/Downtimes/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//****************************************************Go to Status Details page****************************************************//

WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

WebUI.click(findTestObject('Old menu/Monitoring/a_Status Details'))

WebUI.click(findTestObject('Old menu/Monitoring/a_Services'))

//*******************************************************Verify the downtime*******************************************************//

//This put the service status to 'All'
WebUI.selectOptionByValue(findTestObject('Monitoring/Status details/Services/select_Unhandled problems'), 'svc', true)

//These lines search for the service affected by the downtime
def search = WebUI.modifyObjectProperty(findTestObject("General/input_Search"), 'name', 'equals', 'search', true)

def sgFile = TestDataFactory.findTestData('Service group')

WebUI.setText(search, sgFile.getValue('Service', 1))

search = WebUI.modifyObjectProperty(search, 'name', 'equals', 'host_search', true)

def hostFile = TestDataFactory.findTestData('Host data')

WebUI.setText(search, config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_11')

WebUI.delay(1)

WebUI.verifyElementPresent(findTestObject('Monitoring/Downtimes/img_Service downtime icon'), 3)

WebUI.setText(search, config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_12')

WebUI.delay(1)

WebUI.verifyElementPresent(findTestObject('Monitoring/Downtimes/img_Service downtime icon'), 3)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
