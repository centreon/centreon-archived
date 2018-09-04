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


suiteProperties.put('id', 'Test Suites/2.8.x/1.Prerequisites')

suiteProperties.put('name', '1.Prerequisites')

suiteProperties.put('description', '')
 

DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.webui.contribution.WebUiDriverCleaner())
DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.mobile.contribution.MobileDriverCleaner())



RunConfiguration.setExecutionSettingFile("C:\\Users\\mgallardo\\Desktop\\Katalon\\Premiere_automatisation\\Reports\\2.8.x\\1.Prerequisites\\20180903_165834\\execution.properties")

TestCaseMain.beforeStart()

TestCaseMain.startTestSuite('Test Suites/2.8.x/1.Prerequisites', suiteProperties, [new TestCaseBinding('Test Cases/2.8.x/PP manager/Installation PP manager', 'Test Cases/2.8.x/PP manager/Installation PP manager',  null), new TestCaseBinding('Test Cases/2.8.x/PP manager/PP installation', 'Test Cases/2.8.x/PP manager/PP installation',  null), new TestCaseBinding('Test Cases/2.8.x/Host/Host creation', 'Test Cases/2.8.x/Host/Host creation',  null), new TestCaseBinding('Test Cases/2.8.x/PP manager/Poller export', 'Test Cases/2.8.x/PP manager/Poller export',  null), new TestCaseBinding('Test Cases/2.8.x/Service/Services check', 'Test Cases/2.8.x/Service/Services check',  null), new TestCaseBinding('Test Cases/2.8.x/Host/Duplicate_host', 'Test Cases/2.8.x/Host/Duplicate_host',  null), new TestCaseBinding('Test Cases/2.8.x/Host/Host Group creation', 'Test Cases/2.8.x/Host/Host Group creation',  null), new TestCaseBinding('Test Cases/2.8.x/Service/Service group creation', 'Test Cases/2.8.x/Service/Service group creation',  null), new TestCaseBinding('Test Cases/2.8.x/User creation', 'Test Cases/2.8.x/User creation',  null), new TestCaseBinding('Test Cases/2.8.x/Service/Services verification', 'Test Cases/2.8.x/Service/Services verification',  null), new TestCaseBinding('Test Cases/2.8.x/Service/Ping_RTA_Average creation', 'Test Cases/2.8.x/Service/Ping_RTA_Average creation',  null)])
