import React, { Component } from "react";
import * as Centreon from '@centreon/react-components'
import { connect } from 'react-redux';

class ExtensionsRoute extends Component {

  state = {
    widgetsActive: true,
    modulesActive: true,
    not_installed: true,
    installed: true,
    updated: true,
    search: ""
  }

  componentDidMount = () => {
    this.getData();
  }

  onChange = (value, key) => {
    const { filters } = this.state;
    let additionalValues = {};
    if (typeof this.state[key] != 'undefined') {
      additionalValues[key] = value;
    }
    this.setState({
      ...additionalValues,
      filters: {
        ...filters,
        [key]: value
      }
    }, this.getData)
  }

  clearFilters = () => {
    this.setState({
      widgetsActive: true,
      modulesActive: true,
      not_installed: true,
      installed: true,
      updated: true,
      nothingShown:false,
      search: ""
    }, this.getData)
  }

  uploadLicence = () => {
    //TO DO: Pop up
  }

  installAll = () => {
    //TO DO: Call API for install
  }

  updateAll = () => {
    //TO DO: Call API for update
  }

  getParsedGETParamsForExtensions = (callback) => {
    const { installed, not_installed, updated, search } = this.state;
    let params = '';
    let nothingShown = false;
    if(search){
      params += '&search='+search
    }
    if(installed && not_installed && updated){
      callback(params, nothingShown);
    }else{
      if(!updated){
        params += '&updated=false'
      }
      if(installed && !not_installed){
        params += "&installed=true"
      }else if(!installed && not_installed){
        params += "&installed=false"
      }else if(!installed && !not_installed){
        nothingShown = true
      }
      callback(params, nothingShown);
    }
  }

  getData = () => {
    const { getAxiosData } = this.props;
    this.getParsedGETParamsForExtensions((params, nothingShown)=>{
      this.setState({
        nothingShown
      })
      if(!nothingShown){
        getAxiosData({ url: `./api/internal.php?object=centreon_module&action=list${params}`, propKey: 'extensions' })
      }
    })
  }

  render = () => {

    const { remoteData } = this.props;
    const { modulesActive, widgetsActive, not_installed, installed, updated, search, nothingShown } = this.state;

    return (
      <div>
        <Centreon.TopFilters
          fullText={{
            label: "Search:",
            value: search,
            filterKey: 'search'
          }}
          onChange={this.onChange.bind(this)}
          switchers={[
            [
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherTitle: "Status:",
                switcherStatus: "Not installed",
                value: not_installed,
                filterKey: 'not_installed'
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Installed",
                value: installed,
                filterKey: 'installed'
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Update",
                value: updated,
                filterKey: 'updated'
              }
            ],
            [
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherTitle: "Type:",
                switcherStatus: "Module",
                value: modulesActive,
                filterKey: 'modulesActive'
              },
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherStatus: "Widget",
                value: widgetsActive,
                filterKey: 'widgetsActive'
              },
              {
                button: true,
                label: "Clear Filters",
                color: "black",
                buttonType: "bordered",
                onClick: this.clearFilters.bind(this)
              }
            ]
          ]}
        />
        <Centreon.Wrapper>
          <Centreon.Button label={"Update all"} buttonType="regular" customClass="mr-2" color="orange" onClick={this.updateAll.bind(this)} />
          <Centreon.Button label={"Install all"} buttonType="regular" customClass="mr-2" color="green" onClick={this.installAll.bind(this)} />
          <Centreon.Button label={"Upload licence"} buttonType="regular" color="blue" onClick={this.uploadLicence.bind(this)} />
        </Centreon.Wrapper>
        {
          remoteData.extensions && !nothingShown ? (
            <React.Fragment>
              {
                remoteData.extensions.result.module && modulesActive ? (
                  <Centreon.ExtensionsHolder title="Modules" entities={remoteData.extensions.result.module.entities} />
                ) : null
              }
              {
                remoteData.extensions.result.widget && widgetsActive ? (
                  <Centreon.ExtensionsHolder title="Widgets" entities={remoteData.extensions.result.widget.entities} />
                ) : null
              }
            </React.Fragment>
          ) : null
        }

      </div>
    )
  }
}


const mapStateToProps = ({ remoteData }) => ({
  remoteData
})


const mapDispatchToProps = {
  getAxiosData: (data) => {
    return {
      type: '@axios/GET_DATA',
      ...data
    }
  }
};


export default connect(mapStateToProps, mapDispatchToProps)(ExtensionsRoute);