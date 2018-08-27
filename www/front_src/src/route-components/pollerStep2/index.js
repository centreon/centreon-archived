import React, { Component } from "react";
import Form from '../../components/forms/poller/PollerFormStepTwo';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';
import axios from "../../axios";

class PollerStepTwoRoute extends Component {
  
  wizardFormApi = axios('internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer');
  wizardFormSelect = axios('internal.php?object=centreon_configuration_remote&action=getWaitList');

    handleSubmit = data => {
      const {history} = this.props;
      console.log(data)
      return this.wizardFormApi
        .post(data)
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

export default PollerStepTwoRoute;
