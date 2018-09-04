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

//*******************************************************Go to ACL menu page*******************************************************//

WebUI.click(findTestObject('Administration/button_Administration'))

WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))

WebUI.click(findTestObject('Administration/ACL/ACL menu/p_Menus Access'))

//******************************************************Configure the ACL menu*****************************************************//
WebUI.delay(1)

def file = TestDataFactory.findTestData('ACL menu config/ACL menu')

WebUI.setText(findTestObject('Administration/ACL/ACL menu/input_Search'), file.getValue('ACLMenuName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.delay(1)

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals', ACLMenuName, true)

WebUI.click(a)

//This file contains the data used to configure the Administration's part of the ACL menu
def fileHome = TestDataFactory.findTestData('ACL menu config/ACL menu Home')

//These 2 followings elements are used to interact with the images, selects and radios. I modify directly their id to avoid
//storing 100 (or more) different items.
def select = findTestObject('Administration/ACL/ACL menu/ACL menu creation/select_Home')

def img = findTestObject('Administration/ACL/ACL menu/ACL menu creation/img_Home')

//If 'Home' == '1' then the access to some or all the pages is granted and I need to see more precisely
//the rights of the ACL menu. If 'Home' == '0', no access is granted and the job here is done.
if (fileHome.getValue('Home', 1) == '1') {
    WebUI.click(select)

    //img is the cross on the left.
    WebUI.click(img)

    //This configure the Custom Views page
    if (fileHome.getValue('Home/Custom Views', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_0_0', true)

        WebUI.click(img)

        WebUI.scrollToElement(img, 3)

        if (fileHome.getValue('Cust/Edit View', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_0', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Cust/Share View', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_1', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Cust/Widget Parameters', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_2', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Cust/Add Widget', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_3', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Cust/Rotation', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_4', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Cust/Delete View', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_5', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Cust/Add View', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_6', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Cust/Set Default', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0_7', true)

            WebUI.click(select)
        }
        //When 'Home' is checked then every select depending on 'Home' is automatically checked.
        //So if the access to 'Custom Views' is not allowed I need to deselect it.
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_0', true)

        WebUI.click(select)
    }
    
    //This configure the Poller Statistics page
    if (fileHome.getValue('Home/Poller statistics', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_0_1', true)

        WebUI.click(img)

        if (fileHome.getValue('Poller/Broker Statistics', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_1_0', true)

            WebUI.click(select)
        }
        
        if (fileHome.getValue('Poller/Graphs', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_1_1', true)

            WebUI.click(select)
        }
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i0_1', true)

        WebUI.click(select)
    }
}

WebUI.click(findTestObject('General/input_submitC'))

//Wait to be sure Edge correctly modify the ACL menu
WebUI.waitForPageLoad(1)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()

