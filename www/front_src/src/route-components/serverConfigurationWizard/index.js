/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable camelcase */

import React, { Component } from 'react';

import Form from '../../components/forms/ServerConfigurationWizardForm';
import routeMap from '../../route-maps/route-map';
import ProgressBar from '../../components/progressBar';
import BaseWizard from '../../components/forms/baseWizard';

class ServerConfigurationWizardRoute extends Component {
  links = [
    { active: true, number: 1, path: routeMap.serverConfigurationWizard },
    { active: false, number: 2 },
    { active: false, number: 3 },
    { active: false, number: 4 },
  ];

  handleSubmit = ({ server_type }) => {
    const { history } = this.props;
    if (server_type === '1') {
      history.push(routeMap.remoteServerStep1);
    }
    if (server_type === '2') {
      history.push(routeMap.pollerStep1);
    }
  };

  render() {
    const { links } = this;

    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <Form onSubmit={this.handleSubmit.bind(this)} />
      </BaseWizard>
    );
  }
}

export default ServerConfigurationWizardRoute;
