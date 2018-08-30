import React, { Component } from "react";
import Form from '../../components/forms/ServerConfigurationWizardForm';
import routeMap from '../../route-maps';
import ProgressBar from '../../components/progressBar';

class ServerConfigurationWizardRoute extends Component {

    handleSubmit = ({server_type}) => {
      const {history} = this.props;
      if(server_type == 1) {
        history.push(routeMap.remoteServerStep1)
      }
      if(server_type == 2) {
        history.push(routeMap.pollerStep1)
      }
    }

    goToPath = path => {
      const {history} = this.props;
      history.push(path);
    };

    links = [
      {active: true, number: 1, path: this.goToPath.bind(this, routeMap.serverConfigurationWizard)},
      {active: true, number: 2},
      {active: false, number: 3}
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

export default ServerConfigurationWizardRoute;
