import React, { Component } from "react";
import Form from '../../components/forms/poller/PollerFormStepTwo';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';
import axios from "../../axios";
import {connect} from 'react-redux';

class PollerStepTwoRoute extends Component {
  
  wizardFormApi = axios('internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer');
  wizardFormWaitListApi = axios('internal.php?object=centreon_configuration_remote&action=getWaitList');

    // getWaitList = listData => {

    // }

    // componentWillMount(){
    //   getWaitList(listData)
    // }

    handleSubmit = data => {
      const {pollerData} = this.props
      this.wizardFormApi.post('', {...pollerData, ...data})
        .then(response => {
          console.log(response)
        })
        .catch(err => {
          console.log(err)
      });
    };
  
  goToPath = path => {
    const {history} = this.props;
    history.push(path);
  };

  links = [
    {active: true, prevActive: true, number: 1, path: this.goToPath.bind(this, routeMap.serverConfigurationWizard)},
    {active: true, prevActive: true, number: 2, path: this.goToPath.bind(this, routeMap.pollerStep1)},
    {active: true, number: 3},
  ];
    
  render(){
    const {links} = this;
    return (
      <div>
        <ProgressBar links={links} />
        <Form onSubmit={this.handleSubmit.bind(this)}/>
      </div>
    )
  }
}

const mapStateToProps = ({pollerForm}) => ({
  pollerData: pollerForm
});

export default connect(mapStateToProps, null)(PollerStepTwoRoute);
