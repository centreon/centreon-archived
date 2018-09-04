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

//*****************************************************go to Plugin Packs page*****************************************************//

WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

WebUI.delay(1)

WebUI.click(findTestObject('Old menu/Configuration/a_Plugin Packs'))

//*****************************************************install the Plugin Packs****************************************************//

//Installation of the first plugin pack
WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/div_pp-icon'))

WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/img'))

WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/div_In order to install the pl'))

WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/button_Apply'))

//This reload the web page
WebUI.click(findTestObject('Old menu/Configuration/a_Plugin Packs'))

WebUI.delay(1)

//Install the second plugin pack
WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/div_pp-icon_2'))

WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/img'))

WebUI.delay(1)

//This reload the web page
WebUI.click(findTestObject('Old menu/Configuration/a_Plugin Packs'))

WebUI.delay(1)

//Install the third plugin pack
WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/div_pp-icon_1'))

WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/img'))

WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/div_In order to install the pl_1'))

WebUI.click(findTestObject('Configuration/PP manager/Page_Centreon - PP installation/button_Apply'))

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
