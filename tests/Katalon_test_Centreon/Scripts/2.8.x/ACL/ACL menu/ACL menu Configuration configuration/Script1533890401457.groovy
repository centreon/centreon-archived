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

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

WebUI.click(findTestObject('Old menu/Administration/a_Menus Access'))

WebUI.waitForPageLoad(3)

//******************************************************Configure the ACL menu*****************************************************//

def file = TestDataFactory.findTestData('ACL menu config/ACL menu')

WebUI.setText(findTestObject('Administration/ACL/ACL menu/input_Search'), file.getValue('ACLMenuName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.delay(1)

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals', ACLMenuName, true)

WebUI.click(a)

WebUI.delay(1)

//These 3 followings elements are used to interact with the images, selects and radios. I modify directly their id to avoid
//storing 100 (or more) different items.
def select = findTestObject('Administration/ACL/ACL menu/ACL menu creation/select_Home')

def img = findTestObject('Administration/ACL/ACL menu/ACL menu creation/img_Home')

def radio = findTestObject('Administration/ACL/ACL menu/ACL menu creation/radio')

//This file contains the data used to configure the Administration's part of the ACL menu
def fileConfiguration = TestDataFactory.findTestData('ACL menu config/ACL menu Configuration')

//'0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'
WebUI.scrollToElement(select, 3)

//If 'Configuration' == '1' then the access to some or all the pages is granted and I need to see more precisely
//the rights of the ACL menu. If 'Configuration' == '0', no access is granted and the job here is done.
if (fileConfiguration.getValue('Configuration', 1) == '1') {
    select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3', true)

    WebUI.click(select)

    //img is the cross on the left.
    img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3', true)

    WebUI.click(img)

    //This configure the Hosts page
    if (fileConfiguration.getValue('Conf/Hosts', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_0', true)

        WebUI.click(img)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_0', true)

        //The value of the radio to click is : '0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'
        //This value is stored in the Data File.
        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Hosts/Hosts', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_1', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Hosts/hg', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_2', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Hosts/Templates', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_3', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Hosts/Categories', 1), 
            true)

        WebUI.click(radio) //When 'Configuration' is checked then every select depending on 'Configuration' is automatically checked.
        //So if the access to 'Hosts' is not allowed I need to deselect it.
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_0', true)

        WebUI.click(select)
    }
    
    //This configure the Services page
    if (fileConfiguration.getValue('Conf/Services', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_1', true)

        WebUI.click(img)

        WebUI.scrollToElement(img, 3)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_0', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/Services by hosts', 
                1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_1', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/Services by hg', 1), 
            true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_2', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/sg', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_3', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/Templates', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_4', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/Categories', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_5', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/Meta Services', 1), 
            true)

        WebUI.click(radio)
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_1', true)

        WebUI.click(select)

        WebUI.scrollToElement(select, 3)
    }
    
    //This configure the Users page
    if (fileConfiguration.getValue('Conf/Users', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_2', true)

        WebUI.click(img)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_2_0', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Users/Contacts', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_2_1', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Users/ct', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_2_2', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Users/cg', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_2_3', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Users/tp', 1), true)

        WebUI.click(radio)
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_2', true)

        WebUI.click(select)
    }
    
    //This configure the Commands page
    if (fileConfiguration.getValue('Conf/Commands', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_3', true)

        WebUI.click(img)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_3_0', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Com/Checks', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_3_1', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Com/Notifications', 1), 
            true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_3_2', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Com/Discovery', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_3_3', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Com/Miscellaneous', 1), 
            true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_3_4', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Com/Connectors', 1), true)

        WebUI.click(radio)
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_3', true)

        WebUI.click(select)
    }
    
    //This configure the Notifications page
    if (fileConfiguration.getValue('Conf/Notifications', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_4', true)

        WebUI.click(img)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_4_0', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Notif/Escalations', 1), 
            true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_4_1', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Notif/Hosts', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_4_2', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Notif/hg', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_4_3', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Notif/Services', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_4_4', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Notif/sg', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_4_5', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Notif/Meta Services', 1), 
            true)

        WebUI.click(radio)
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_4', true)

        WebUI.click(select)
    }
    
    //This configure the SNMP Traps page
    if (fileConfiguration.getValue('Conf/SNMP Traps', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_5', true)

        WebUI.click(img)

        WebUI.scrollToElement(img, 3)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_5_0', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('SNMP/SNMP Traps', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_5_1', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('SNMP/Manufacturer', 1), 
            true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_5_2', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('SNMP/Group', 1), true)

        WebUI.click(radio)

        if (fileConfiguration.getValue('SNMP/MIBs', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_5_3', true)

            WebUI.click(select)
        }
        
        if (fileConfiguration.getValue('SNMP/Generate', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_5_4', true)

            WebUI.click(select)
        }
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_5', true)

        WebUI.click(select)

        WebUI.scrollToElement(select, 3)
    }
    
    //This configure the Knowledge Base page
    if (fileConfiguration.getValue('Conf/Knowledge Base', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_6', true)

        WebUI.click(img)

        if (fileConfiguration.getValue('KB/Hosts', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_6_0', true)

            WebUI.click(select)
        }
        
        if (fileConfiguration.getValue('KB/Services', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_6_1', true)

            WebUI.click(select)
        }
        
        if (fileConfiguration.getValue('KB/ht', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_6_2', true)

            WebUI.click(select)
        }
        
        if (fileConfiguration.getValue('KB/st', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_6_3', true)

            WebUI.click(select)
        }
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_6', true)

        WebUI.click(select)
    }
    
    //This configure the Plugin Packs page
    if (fileConfiguration.getValue('Conf/Plugin Packs', 1) == '1') {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_7', true)

        WebUI.click(img)

        WebUI.scrollToElement(img, 3)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_7_0', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('PP/Manager', 1), true)

        WebUI.click(radio)

        if (fileConfiguration.getValue('PP/Plugin pack documentation', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_7_1', true)

            WebUI.click(select)
        }
    } else {
        select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_7', true)

        WebUI.click(select)

        WebUI.scrollToElement(select, 3)
    }
    
    radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_8', true)

    radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Conf/Pollers', 1), true)

    WebUI.click(radio)

    //This configure the Pollers page
    if ((fileConfiguration.getValue('Conf/Pollers', 1) == '1') || (fileConfiguration.getValue('Conf/Pollers', 1) == '2')) {
        img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_8', true)

        WebUI.click(img)

        if (fileConfiguration.getValue('Pollers/Export configuration', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_8_0', true)

            WebUI.click(select)
        }
        
        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_8_1', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Pollers/Pollers', 1), true)

        WebUI.click(radio)

        radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_8_2', true)

        radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Pollers/Engine configuration', 
                1), true)

        WebUI.click(radio)

        if (fileConfiguration.getValue('Pollers/Broker configuration', 1) == '1') {
            img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_8_3', true)

            WebUI.click(img)

            if (fileConfiguration.getValue('Brok/Wizard', 1) == '0') {
                select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_8_3_0', true)

                WebUI.click(select)
            }
            
            if (fileConfiguration.getValue('Brok/WizardAjax', 1) == '0') {
                select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_8_3_1', true)

                WebUI.click(select)
            }
        } else {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_8_3', true)

            WebUI.click(select)
        }
        
        if (fileConfiguration.getValue('Pollers/Resources', 1) == '0') {
            select = WebUI.modifyObjectProperty(select, 'id', 'equals', 'i3_8_4', true)

            WebUI.click(select)
        }
    }
}

WebUI.click(findTestObject('General/input_submitC'))

//Wait to be sure Edge correctly modify the ACL menu
WebUI.waitForPageLoad(1)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
