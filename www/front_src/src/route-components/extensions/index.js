import React, {Component} from "react";
import * as Centreon from '@centreon/react-components'

class ExtensionsRoute extends Component {
  render() {
    
    return (
      <div>
        <div className="container container-gray">
          <Centreon.Wrapper>
            <div className="container__row">
              <div className="container__col-md-3 container__col-xs-12">
                <Centreon.SearchLive label="Search:"/>
              </div>
              <div className="container__col-md-9 container__col-xs-12">
                <div className="container__row">
                  <div className="container__col-sm-6 container__col-xs-12">
                    <div className="container__row">
                      <Centreon.Switcher
                        customClass="container__col-md-4 container__col-xs-4"
                        switcherTitle="Status:"
                        switcherStatus="Not installed"/>
                      <Centreon.Switcher
                        customClass="container__col-md-4 container__col-xs-4"
                        switcherStatus="Installed"/>
                      <Centreon.Switcher
                        customClass="container__col-md-4 container__col-xs-4"
                        switcherStatus="Update"/>
                    </div>
                  </div>
                  <div className="container__col-sm-6 container__col-xs-12">
                    <div className="container__row">
                      <Centreon.Switcher
                        customClass="container__col-sm-3 container__col-xs-4"
                        switcherTitle="Type:"
                        switcherStatus="Module"/>
                      <Centreon.Switcher
                        customClass="container__col-sm-3 container__col-xs-4"
                        switcherStatus="Widget"/>
                      <div className="container__col-sm-6 container__col-xs-4 center-vertical mt-1">
                        <Centreon.Button label={"Clear Filters"} buttonType="bordered" color="black"/>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Centreon.Wrapper>
        </div>
        <section className="buttons content-wrapper">
          <Centreon.Button label={"Update all"} buttonType="regular" color="orange"/>
          <Centreon.Button label={"Install all"} buttonType="regular" color="green"/>
          <Centreon.Button label={"Upload licence"} buttonType="regular" color="blue"/>
        </section>
        <section className="content-wrapper">
          <Centreon.HorizontalLineContent hrTitle="Modules"/>
          <Centreon.Card
            itemBorderColor="orange"
            itemFooterColor="orange"
            itemFooterLabel="Some label for the footer"
          >
            <Centreon.IconInfo iconName="state" />
            <div className="custom-title-heading">
              <Centreon.Title icon="object" label="Test Title" />
              <Centreon.Subtitle label="Test Subtitle" />
            </div>
            <Centreon.Button
              buttonType="regular"
              color="orange"
              label="Button example"
              iconActionType="update"
            />
            <Centreon.ButtonAction buttonActionType="delete" buttonIconType="delete" />
          </Centreon.Card>

        </section>
        <section class="content-wrapper">
          <div class="content-hr">
            <span class="content-hr-title">Widgets</span>
          </div>

          <div class="card">
            <div class="card-items">
              <div class="container__row">
                <div class="container__col-md-3 container__col-sm-6 container__col-xs-12">
                  <div class="card-item card-item-bordered-orange">
                    <span class="info info-state"></span>
                    <div class="custom-title-heading">
                      <h2 class="custom-title">
                        <span class="custom-title-icon custom-title-icon-puzzle"></span>Plugin pack manager</h2>
                      <h4 class="custom-subtitle">by Centreon</h4>
                    </div>
                    <button class="button button-regular-orange linear">Available 3.1.5<span class="icon-action icon-action-update"></span>
                    </button>
                    <span class="card-item-footer card-item-footer-blue">Licence expire 12/08/2018</span>
                  </div>
                </div>
                <div class="container__col-md-3 container__col-sm-6 container__col-xs-12">
                  <div class="card-item card-item-bordered-green">
                    <span class="info info-state"></span>
                    <div class="custom-title-heading">
                      <h2 class="custom-title">
                        <span class="custom-title-icon custom-title-icon-puzzle"></span>Engine-status</h2>
                      <h4 class="custom-subtitle">by Centreon</h4>
                    </div>
                    <button class="button button-bordered button-bordered-blue linear">Version 3.1.5</button>
                    <span class="button-action button-action-delete">
                      <span class="button-action-icon-delete"></span>
                    </span>
                    <span class="card-item-footer card-item-footer-red">Licence expire 12/08/2018</span>
                  </div>
                </div>
                <div class="container__col-md-3 container__col-sm-6 container__col-xs-12">
                  <div class="card-item card-item-bordered-gray">
                    <div class="custom-title-heading">
                      <h2 class="custom-title">
                        <span class="custom-title-icon custom-title-icon-puzzle"></span>Engine-status</h2>
                      <h4 class="custom-subtitle">by Centreon</h4>
                    </div>
                    <button class="button button-regular-green linear">Available 3.1.5<span class="icon-action icon-action-add"></span>
                    </button>
                  </div>
                </div>
                <div class="container__col-md-3 container__col-sm-6 container__col-xs-12">
                  <div class="card-item card-item-bordered-gray">
                    <span class="info info-state"></span>
                    <div class="custom-title-heading">
                      <h2 class="custom-title">
                        <span class="custom-title-icon custom-title-icon-puzzle"></span>Engine-status</h2>
                      <h4 class="custom-subtitle">by Centreon</h4>
                    </div>
                    <button class="button button-regular-green linear">Available 3.1.5<span class="icon-action icon-action-add"></span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>
      </div>

    )
  }
}

export default ExtensionsRoute;