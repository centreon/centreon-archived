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


suiteProperties.put('id', 'Test Suites/2.8.x/Check recurrent downtimes')

suiteProperties.put('name', 'Check recurrent downtimes')

suiteProperties.put('description', '')
 

DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.webui.contribution.WebUiDriverCleaner())
DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.mobile.contribution.MobileDriverCleaner())



RunConfiguration.setExecutionSettingFile("C:\\Users\\mgallardo\\Desktop\\Katalon\\Premiere_automatisation\\Reports\\2.8.x\\Check recurrent downtimes\\20180903_165848\\execution.properties")

TestCaseMain.beforeStart()

TestCaseMain.startTestSuite('Test Suites/2.8.x/Check recurrent downtimes', suiteProperties, [new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_specific_date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_specific_date',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_specific_date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_specific_date',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_group_weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_group_weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_group_monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_group_monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_group_specific_date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_host_group_specific_date',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_group_weekly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_group_weekly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_group_monthly', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_group_monthly',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_group_specific_date', 'Test Cases/2.8.x/Downtimes/Recurrent downtimes/Check/check_service_group_specific_date',  null)])
