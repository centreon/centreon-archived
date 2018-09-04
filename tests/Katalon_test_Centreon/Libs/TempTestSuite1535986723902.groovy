import com.kms.katalon.core.logging.KeywordLogger
import com.kms.katalon.core.exception.StepFailedException
import com.kms.katalon.core.reporting.ReportUtil
import com.kms.katalon.core.main.TestCaseMain
import com.kms.katalon.core.testdata.TestDataColumn
import groovy.lang.MissingPropertyException
import com.kms.katalon.core.testcase.TestCaseBinding
import com.kms.katalon.core.driver.internal.DriverCleanerCollector
import com.kms.katalon.core.model.FailureHandling
import com.kms.katalon.core.configuration.RunConfiguration
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import internal.GlobalVariable as GlobalVariable

Map<String, String> suiteProperties = new HashMap<String, String>();


suiteProperties.put('id', 'Test Suites/2.8.x/Initiate recurrent downtimes')

suiteProperties.put('name', 'Initiate recurrent downtimes')

suiteProperties.put('description', '')
 

DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.webui.contribution.WebUiDriverCleaner())
DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.mobile.contribution.MobileDriverCleaner())



RunConfiguration.setExecutionSettingFile("C:\\Users\\mgallardo\\Desktop\\Katalon\\Premiere_automatisation\\Reports\\2.8.x\\Initiate recurrent downtimes\\20180903_165839\\execution.properties")

TestCaseMain.beforeStart()

TestCaseMain.startTestSuite('Test Suites/2.8.x/Initiate recurrent downtimes', suiteProperties, [new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host group weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host group weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service group weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service group weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host group monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host group monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service group monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service group monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host specific date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host specific date',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service specific date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service specific date',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host group specific date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Host group specific date',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service group specific date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Initiate/Service group specific date',  null)])
