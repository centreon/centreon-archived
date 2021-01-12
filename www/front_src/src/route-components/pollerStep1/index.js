/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-shadow */
/* eslint-disable react/no-unused-state */

import React, { Component } from 'react';

import { connect } from 'react-redux';

import Form from '../../components/forms/poller/PollerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import BaseWizard from '../../components/forms/baseWizard';
import axios from '../../axios';

class PollerStepOneRoute extends Component {
  links = [
    {
      active: true,
      prevActive: true,
      number: 1,
      path: routeMap.serverConfigurationWizard,
    },
    { active: true, number: 2, path: routeMap.pollerStep1 },
    { active: false, number: 3 },
    { active: false, number: 4 },
  ];

  state = {
    error: null,
    waitList: null,
  };

  wizardFormWaitListApi = axios(
    'internal.php?object=centreon_configuration_remote&action=getPollerWaitList',
  );

  getWaitList = () => {
    this.wizardFormWaitListApi
      .post()
      .then((response) => {
        this.setState({ waitList: response.data });
      })
      .catch(() => {
        this.setState({ waitList: [] });
      });
  };

  componentDidMount = () => {
    this.getWaitList();
  };

  handleSubmit = (data) => {
    const { history, setPollerWizard } = this.props;
    setPollerWizard(data);
    history.push(routeMap.pollerStep2);
  };

  render() {
    const { links } = this;
    const { waitList } = this.state;
    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <Form
          onSubmit={this.handleSubmit.bind(this)}
          initialValues={{}}
          waitList={waitList}
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
export default connect(mapStateToProps, mapDispatchToProps)(PollerStepOneRoute);
