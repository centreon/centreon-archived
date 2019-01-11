import React, { Component } from "react";
import * as Centreon from '@centreon/react-components'
import { connect } from 'react-redux';

class ExtensionsRoute extends Component {

  state = {
    widgetsActive: true,
    modulesActive: true,
    filters: {
      search: null,

    }
  }

  componentDidMount = () => {
    this.getData();
  }

  onChange = (value, key) => {
    const { filters } = this.state;
    console.log(value, key)
    this.setState({
      filters: {
        ...filters,
        [key]: value
      }
    })
  }

  clearFilters = () => {
    console.log('here')
  }

  getData = () => {
    const { getAxiosData } = this.props;
    getAxiosData({ url: `./api/internal.php?object=centreon_module&action=list`, propKey: 'extensions' })
  }

  render = () => {

    const { remoteData } = this.props;
    const { modulesActive, widgetsActive } = this.state;


    return (
      <div>
        <Centreon.TopFilters
          fullText={{
            label: "Search:",
            filterKey: 'search'
          }}
          onChange={this.onChange.bind(this)}
          switchers={[
            [
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherTitle: "Status:",
                switcherStatus: "Not installed",
                defaultValue: false,
                filterKey: 'not_installed'
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Installed",
                defaultValue: false,
                filterKey: 'installed'
              },
              {
                customClass: "container__col-md-4 container__col-xs-4",
                switcherStatus: "Update",
                defaultValue: false,
                filterKey: 'updated'
              }
            ],
            [
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherTitle: "Type:",
                switcherStatus: "Module",
                defaultValue: false,
                filterKey: 'type_module'
              },
              {
                customClass: "container__col-sm-3 container__col-xs-4",
                switcherStatus: "Widget",
                defaultValue: false,
                filterKey: 'type_widget'
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
          <Centreon.Button label={"Update all"} buttonType="regular" customClass="mr-2" color="orange" />
          <Centreon.Button label={"Install all"} buttonType="regular" customClass="mr-2" color="green" />
          <Centreon.Button label={"Upload licence"} buttonType="regular" color="blue" />
        </Centreon.Wrapper>
        {
          remoteData.extensions ? (
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