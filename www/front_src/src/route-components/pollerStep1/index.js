import React, { Component } from "react";
import Form from '../../components/forms/poller/PollerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';
import axios from "../../axios";

import {connect} from 'react-redux';

class PollerStepOneRoute extends Component {

  state = {
    error: null
  }

  wizardFormApi = axios('internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer');

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
    {active: true, number: 2, path: this.goToPath.bind(this, routeMap.pollerStep1)}
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

// const mapStateToProps = ({pollerForm}) => ({
//   pollerData: pollerForm
// });

const mapDispatchToProps = {
  setPollerWizard
}
export default connect(null, mapDispatchToProps)(PollerStepOneRoute);
