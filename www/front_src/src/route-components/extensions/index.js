import React, {Component} from "react";
import * as Centreon from '@centreon/react-components'

class ExtensionsRoute extends Component {
  render() {
    
    return (
      <div>
        <Centreon.TopFilters
            fullText={{
                label: "Search:",
                onChange: (a) => {
                    console.log(a)
                }
            }}
            switchers={[
                [
                    {
                        customClass: "container__col-md-4 container__col-xs-4",
                        switcherTitle: "Status:",
                        switcherStatus: "Not installed",
                        defaultValue:false,
                        onChange:(value)=>{
                            console.log(value)
                        }
                    },
                    {
                        customClass: "container__col-md-4 container__col-xs-4",
                        switcherStatus: "Installed",
                        defaultValue:false,
                        onChange:(value)=>{
                            console.log(value)
                        }
                    },
                    {
                        customClass: "container__col-md-4 container__col-xs-4",
                        switcherStatus: "Update",
                        defaultValue:false,
                        onChange:(value)=>{
                            console.log(value)
                        }
                    }
                ],
                [
                    {
                        customClass: "container__col-sm-3 container__col-xs-4",
                        switcherTitle: "Type:",
                        switcherStatus: "Module",
                        defaultValue:false,
                        onChange:(value)=>{
                            console.log(value)
                        }
                    },
                    {
                        customClass: "container__col-sm-3 container__col-xs-4",
                        switcherStatus: "Update",
                        defaultValue:false,
                        onChange:(value)=>{
                            console.log(value)
                        }
                    },
                    {
                        button:true,
                        label:"Clear Filters",
                        color:"black",
                        buttonType:"bordered",
                        onClick: ()=>{
                            console.log('Clear filters clicked')
                        }
                    }
                ]
            ]}
        />
        <Centreon.Wrapper>
          <Centreon.Button label={"Update all"} buttonType="regular" customClass="mr-2" color="orange"/>
          <Centreon.Button label={"Install all"} buttonType="regular" customClass="mr-2" color="green"/>
          <Centreon.Button label={"Upload licence"} buttonType="regular" color="blue"/>
        </Centreon.Wrapper>
        <Centreon.Wrapper>
          <Centreon.HorizontalLineContent hrTitle="Modules"/>
          <Centreon.Card>
            <div className="container__row">
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem
                  itemBorderColor="orange"
                  itemFooterColor="red"
                  itemFooterLabel="Licence expire 12/08/2018">
                  <Centreon.IconInfo iconName="state"/>
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Engine-status"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button
                    buttonType="regular"
                    color="orange"
                    label="Available 3.1.5"
                    iconActionType="update"/>
                </Centreon.CardItem>
              </div>
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem
                  itemBorderColor="green"
                  itemFooterColor="orange"
                  itemFooterLabel="Licence expire 12/08/2018">
                  <Centreon.IconInfo iconName="state"/>
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Engine-status"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button buttonType="bordered" color="blue" label="Available 3.1.5"/>
                  <Centreon.ButtonAction buttonActionType="delete" buttonIconType="delete"/>
                </Centreon.CardItem>
              </div>
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem itemBorderColor="gray">
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Engine-status"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button buttonType="regular" color="green"  label="Available 3.1.5"><Centreon.IconContent iconContentType="add"/></Centreon.Button>
                </Centreon.CardItem>
              </div>
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem itemBorderColor="gray">
                  <Centreon.IconInfo iconName="state"/>
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Engine-status"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button buttonType="regular" color="green"  label="Available 3.1.5"><Centreon.IconContent iconContentType="add"/></Centreon.Button>
                </Centreon.CardItem>
              </div>
            </div>
          </Centreon.Card>
        </Centreon.Wrapper>
        <Centreon.Wrapper>
          <Centreon.HorizontalLineContent hrTitle="Widgets"/>
          <Centreon.Card>
            <div className="container__row">
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem
                  itemBorderColor="orange"
                  itemFooterColor="red"
                  itemFooterLabel="Licence expire 12/08/2018">
                  <Centreon.IconInfo iconName="puzzle"/>
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Plugin pack manager"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button
                    buttonType="regular"
                    color="orange"
                    label="Available 3.1.5"
                    iconActionType="update"/>
                </Centreon.CardItem>
              </div>
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem
                  itemBorderColor="green"
                  itemFooterColor="orange"
                  itemFooterLabel="Licence expire 12/08/2018">
                  <Centreon.IconInfo iconName="puzzle"/>
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Plugin pack manager"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button buttonType="bordered" color="blue" label="Available 3.1.5"/>
                  <Centreon.ButtonAction buttonActionType="delete" buttonIconType="delete"/>
                </Centreon.CardItem>
              </div>
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem itemBorderColor="gray">
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Plugin pack manager"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button buttonType="regular" color="green"  label="Available 3.1.5"><Centreon.IconContent iconContentType="add"/></Centreon.Button>
                </Centreon.CardItem>
              </div>
              <div className="container__col-md-3 container__col-sm-6 container__col-xs-12">
                <Centreon.CardItem itemBorderColor="gray">
                  <Centreon.IconInfo iconName="puzzle"/>
                  <div className="custom-title-heading">
                    <Centreon.Title icon="object" label="Engine-status"/>
                    <Centreon.Subtitle label="by Centreon"/>
                  </div>
                  <Centreon.Button buttonType="regular" color="green"  label="Available 3.1.5"><Centreon.IconContent iconContentType="add"/></Centreon.Button>
                </Centreon.CardItem>
              </div>
            </div>
          </Centreon.Card>
        </Centreon.Wrapper>
      </div>
    )
  }
}

export default ExtensionsRoute;