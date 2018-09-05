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
import org.junit.After as After
import org.openqa.selenium.Keys as Keys

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//***************************************************go to Status Details services*************************************************//

CustomKeywords.'custom.NavigationMonitoring.accessStatusDetailsServices'()

//***************************************************verify the services status****************************************************//

WebUI.selectOptionByValue(findTestObject('Monitoring/Status details/Services/select_Unhandled Problems'), 
    'svc', true)

//This file contains a list of services linked to my host
def servicesFile = TestDataFactory.findTestData('Services')

//This file contains the name of the service concerned
def hostFile = TestDataFactory.findTestData('Host data')

def search = WebUI.modifyObjectProperty(findTestObject('General/input_Search'), 'name', 'equals', 'host_search', true)

//Search for the specific host
WebUI.setText(search, config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1')

search = WebUI.modifyObjectProperty(search, 'name', 'equals', 'search', true)

def nbr

//Check the box on the left of each service. It goes through the list of services
for (def index : (servicesFile.getRowNumbers()..1)) {
	//Search for the specific service
    WebUI.setText(search, servicesFile.getValue('Services', index))

	WebUI.delay(1)

	nbr = 0
		
    //If Centreon had no time enough to check the service, the programm will wait 20 seconds before refreshing
    while (WebUI.verifyElementPresent(findTestObject('Monitoring/Status details/Services/span_PENDING'),
		1, FailureHandling.OPTIONAL) && nbr < 30) {
        WebUI.delay(1)
		
		nbr++

		//This refresh the list of services
        WebUI.click(findTestObject('Monitoring/Status details/Services/img_refresh'))
    }
    
    //While the service is not in PENDING the test is successfull
    WebUI.verifyElementNotPresent(findTestObject('Monitoring/Status details/Services/span_PENDING'), 3)
}

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
