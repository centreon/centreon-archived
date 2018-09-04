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


suiteProperties.put('id', 'Test Suites/2.8.x/ACL')

suiteProperties.put('name', 'ACL')

suiteProperties.put('description', '')
 

DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.webui.contribution.WebUiDriverCleaner())
DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.mobile.contribution.MobileDriverCleaner())



RunConfiguration.setExecutionSettingFile("C:\\Users\\mgallardo\\Desktop\\Katalon\\Premiere_automatisation\\Reports\\2.8.x\\ACL\\20180903_165843\\execution.properties")

TestCaseMain.beforeStart()

TestCaseMain.startTestSuite('Test Suites/2.8.x/ACL', suiteProperties, [new TestCaseBinding('Test Cases/2.8.x/ACL/ACL group creation', 'Test Cases/2.8.x/ACL/ACL group creation',  null), new TestCaseBinding('Test Cases/2.8.x/ACL/ACL resource creation', 'Test Cases/2.8.x/ACL/ACL resource creation',  null), new TestCaseBinding('Test Cases/2.8.x/ACL/ACL menu/ACL menu creation', 'Test Cases/2.8.x/ACL/ACL menu/ACL menu creation',  null), new TestCaseBinding('Test Cases/2.8.x/ACL/ACL action creation', 'Test Cases/2.8.x/ACL/ACL action creation',  null), new TestCaseBinding('Test Cases/2.8.x/Install widgets', 'Test Cases/2.8.x/Install widgets',  null), new TestCaseBinding('Test Cases/2.8.x/ACL/ACL menu/ACL menu check', 'Test Cases/2.8.x/ACL/ACL menu/ACL menu check',  null), new TestCaseBinding('Test Cases/2.8.x/ACL/ACL resource check', 'Test Cases/2.8.x/ACL/ACL resource check',  null)])
