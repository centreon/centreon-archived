import React, { Component } from "react";
import Form from '../../components/forms/poller/PollerFormStepTwo';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';
import axios from "../../axios";
import { connect } from 'react-redux';

class PollerStepTwoRoute extends Component {
  
  links = [
    { active: true, prevActive: true, number: 1, path: routeMap.serverConfigurationWizard },
    { active: true, prevActive: true, number: 2, path: routeMap.pollerStep1 },
    { active: true, number: 3 },
    {active: false, number: 4},
  ];

  wizardFormApi = axios('internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer');
  wizardFormWaitListApi = axios('internal.php?object=centreon_configuration_remote&action=getWaitList');

  handleSubmit = data => {
    const { pollerData } = this.props
    this.wizardFormApi.post('', { ...pollerData, ...data })
      .then(response => {
        console.log(response)
      })
      .catch(err => {
        console.log(err)
      });
  };


  render() {
    const { links } = this;
    return (
      <div>
        <ProgressBar links={links} />
        <Form onSubmit={this.handleSubmit.bind(this)} />
      </div>
    )
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm
});

export default connect(mapStateToProps, null)(PollerStepTwoRoute);
