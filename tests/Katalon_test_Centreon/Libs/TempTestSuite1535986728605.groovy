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


suiteProperties.put('id', 'Test Suites/2.8.x/Downtimes')

suiteProperties.put('name', 'Downtimes')

suiteProperties.put('description', '')
 

DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.webui.contribution.WebUiDriverCleaner())
DriverCleanerCollector.getInstance().addDriverCleaner(new com.kms.katalon.core.mobile.contribution.MobileDriverCleaner())



RunConfiguration.setExecutionSettingFile("C:\\Users\\mgallardo\\Desktop\\Katalon\\Premiere_automatisation\\Reports\\2.8.x\\Downtimes\\20180903_165848\\execution.properties")

TestCaseMain.beforeStart()

TestCaseMain.startTestSuite('Test Suites/2.8.x/Downtimes', suiteProperties, [new TestCaseBinding('Test Cases/2.8.x/PP manager/Poller export', 'Test Cases/2.8.x/PP manager/Poller export',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host and its services', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host and its services',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a servicegroup', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a servicegroup',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a hostgroup', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a hostgroup',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service (Flexible)', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service (Flexible)',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host through Monitoring page', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host through Monitoring page',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host and its services through Monitoring page', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host and its services through Monitoring page',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service through Monitoring page', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service through Monitoring page',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host through Status Details page', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host through Status Details page',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host and its services through Status Details page', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a host and its services through Status Details page',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service through Status Details page', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a service through Status Details page',  null), new TestCaseBinding('Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a hostgroup and related services', 'Test Cases/2.8.x/Downtimes/Realtime downtimes/Downtime on a hostgroup and related services',  null)])
