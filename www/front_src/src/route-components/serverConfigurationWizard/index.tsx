/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable camelcase */

import React, { Component } from 'react';
import Form from '../../components/forms/ServerConfigurationWizardForm';
import routeMap from '../../route-maps/route-map';
import ProgressBar from '../../components/progressBar';

interface Props {
  history: object;
}

interface Submit {
  server_type: string;
}

class ServerConfigurationWizardRoute extends Component {
  private links = [
    { active: true, number: 1, path: routeMap.serverConfigurationWizard },
    { active: false, number: 2 },
    { active: false, number: 3 },
    { active: false, number: 4 },
  ];

  private handleSubmit = ({ server_type }: Submit) => {
    const { history } = this.props;
    if (server_type === '1') {
      history.push(routeMap.remoteServerStep1);
    }
    if (server_type === '2') {
      history.push(routeMap.pollerStep1);
    }
  };

  public render() {
    const { links } = this;
    return (
      <div>
        <ProgressBar links={links} />
        <Form onSubmit={this.handleSubmit.bind(this)} />
      </div>
    );
  }
}

export default ServerConfigurationWizardRoute;
