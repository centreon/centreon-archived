/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-shadow */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';

import { connect } from 'react-redux';

import Form from '../../components/forms/remoteServer/RemoteServerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import axios from '../../axios';
import BaseWizard from '../../components/forms/baseWizard';

class RemoteServerStepOneRoute extends Component {
  links = [
    {
      active: true,
      number: 1,
      path: routeMap.serverConfigurationWizard,
      prevActive: true,
    },
    { active: true, number: 2, path: routeMap.remoteServerStep1 },
    { active: false, number: 3 },
    { active: false, number: 4 },
  ];

  state = {
    waitList: null,
    defaultCentralIp: null,
  };

  wizardFormWaitListApi = axios(
    'internal.php?object=centreon_configuration_remote&action=getWaitList',
  );

  wizardFormDefaultCentralApi = axios(
    'internal.php?object=centreon_configuration_remote&action=getCentralDefaultIp',
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

  getDefaultCentralIp = () => {
    this.wizardFormDefaultCentralApi
      .post()
      .then((response) => {
        this.setState({ defaultCentralIp: response.data})
      })
      .catch(() => {
        this.setState({ defaultCentralIp: null });
      });
  }

  componentDidMount = () => {
    this.getWaitList();
    this.getDefaultCentralIp();
  };

  handleSubmit = (data) => {
    const { history, setPollerWizard } = this.props;
    setPollerWizard(data);
    history.push(routeMap.remoteServerStep2);
  };

  render() {
    const { links } = this;
    const { pollerData } = this.props;
    const { waitList, defaultCentralIp } = this.state;
    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <Form
          initialValues={{ ...pollerData, centreon_central_ip: defaultCentralIp }}
          waitList={waitList}
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

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(RemoteServerStepOneRoute);
