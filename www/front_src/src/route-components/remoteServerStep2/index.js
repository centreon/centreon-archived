import React, { Component } from "react";
import Form from '../../components/forms/remoteServer/RemoteServerFormStepTwo';
import routeMap from '../../route-maps';
import ProgressBar from '../../components/progressBar';

class RemoteServerStepTwoRoute extends Component {
  links = [
    { active: true, prevActive: true, number: 1, path: routeMap.serverConfigurationWizard },
    { active: true, prevActive: true, number: 2, path: routeMap.remoteServerStep1 },
    { active: true, number: 3 },
    {active: false, number: 4},
  ];

  handleSubmit = (data) => {
    console.log(data);
  }

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

export default RemoteServerStepTwoRoute;
