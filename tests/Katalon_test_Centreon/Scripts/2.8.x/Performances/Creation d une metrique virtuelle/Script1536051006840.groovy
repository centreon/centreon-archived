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

//********************************************************Go to Metrics page*******************************************************//

CustomKeywords.'custom.NavigationMonitoring.accessMetrics'()

WebUI.waitForPageLoad(3)

//*****************************************************Create a virtual metric*****************************************************//

WebUI.click(findTestObject('General/a_Add'))

def metricFile = TestDataFactory.findTestData('Virtual metric')

WebUI.setText(findTestObject('Monitoring/Performances/Metrics/input_vmetric_name'), metricFile.getValue('Metric name', 1))

WebUI.click(findTestObject('Monitoring/Performances/Metrics/span_Linked Host Services'))

//0 is for CDEF, 1 is for VDEF
WebUI.selectOptionByValue(findTestObject('Monitoring/Performances/Metrics/select_DEF Type'),
	metricFile.getValue('DEF Type', 1), true)

//These following lines configures the host service
def hostFile = TestDataFactory.findTestData('Host data')

def servicesFile = TestDataFactory.findTestData('Services')

def str = config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1 - ' + servicesFile.getValue(1, 5)

WebUI.setText(findTestObject('Monitoring/Performances/Metrics/input_Linked Host Services'), str)

def element = WebUI.modifyObjectProperty(findTestObject('General/div'), 'title', 'equals',  str, true)

WebUI.click(element)

//This line is to configure the RPN
WebUI.setText(findTestObject('Monitoring/Performances/Metrics/textarea_rpn_function'), metricFile.getValue('RPN', 1))

WebUI.scrollToElement(findTestObject('General/input_submitA'), 2)

WebUI.click(findTestObject('General/input_submitA'))

WebUI.verifyElementPresent(findTestObject('General/a_Add'), 2)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
