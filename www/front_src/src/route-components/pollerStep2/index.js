import React, { Component } from "react";
import Form from '../../components/forms/poller/PollerFormStepTwo';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';
import axios from "../../axios";
import { connect } from 'react-redux';
import { SubmissionError } from 'redux-form';

class PollerStepTwoRoute extends Component {

  state = {
    pollers: null
  }

  links = [
    { active: true, prevActive: true, number: 1, path: routeMap.serverConfigurationWizard },
    { active: true, prevActive: true, number: 2, path: routeMap.pollerStep1 },
    { active: true, number: 3 },
    { active: false, number: 4 },
  ];

  pollerListApi = axios('internal.php?object=centreon_configuration_poller&action=list');
  wizardFormApi = axios('internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer');


  getPollers = () => {
    this.pollerListApi.get()
      .then(response => {
        this.setState({ pollers: response.data })
      });
  }

  componentDidMount = () => {
    this.getPollers()
  }

  handleSubmit = (data) => {
    const { history, pollerData } = this.props;
    let postData = { ...data, ...pollerData };
    return this.wizardFormApi.post('', postData)
      .then(response => {

      })
      .catch(err => {
        throw new SubmissionError({ _error: err });
      });
  }


  render() {
    const { links } = this;
    const { pollerData } = this.props;
    const { pollers } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <Form pollers={pollers} initialValues={pollerData} onSubmit={this.handleSubmit.bind(this)} />
      </div>
    )
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm
});

export default connect(mapStateToProps, null)(PollerStepTwoRoute);
