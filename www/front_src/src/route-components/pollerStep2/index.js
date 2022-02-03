/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-shadow */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';

import { connect } from 'react-redux';
import { SubmissionError } from 'redux-form';
import axios from 'axios';

import Form from '../../components/forms/poller/PollerFormStepTwo';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import BaseWizard from '../../components/forms/baseWizard';

class PollerStepTwoRoute extends Component {
  state = {
    pollers: [],
  };

  links = [
    {
      active: true,
      number: 1,
      path: routeMap.serverConfigurationWizard,
      prevActive: true,
    },
    { active: true, number: 2, path: routeMap.pollerStep1, prevActive: true },
    { active: true, number: 3 },
    { active: false, number: 4 },
  ];

  componentDidMount() {
    this.getPollers();
  }

  getPollers = () => {
    axios
      .post(
        './api/internal.php?object=centreon_configuration_remote&action=getRemotesList',
      )
      .then((response) => {
        this.setState({ pollers: response.data });
      });
  };

  handleSubmit = (data) => {
    const { history, pollerData, setPollerWizard } = this.props;
    const postData = { ...data, ...pollerData };
    postData.server_type = 'poller';

    return axios
      .post(
        './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer',
        postData,
      )
      .then((response) => {
        setPollerWizard({ submitStatus: response.data.success });
        if (pollerData.linked_remote_master) {
          history.push(routeMap.pollerStep3);
        } else {
          history.push(routeMap.pollerList);
        }
      })
      .catch((err) => {
        throw new SubmissionError({ _error: new Error(err.response.data) });
      });
  };

  render() {
    const { links } = this;
    const { pollerData } = this.props;
    const { pollers } = this.state;

    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <Form
          initialValues={pollerData}
          pollers={pollers}
          onSubmit={this.handleSubmit.bind(this)}
        />
      </BaseWizard>
    );
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setPollerWizard,
};

export default connect(mapStateToProps, mapDispatchToProps)(PollerStepTwoRoute);
