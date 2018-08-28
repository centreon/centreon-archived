import React, { Component } from "react";
import Form from '../../components/forms/ServerConfigurationWizardForm';
import routeMap from '../../route-maps';
import ProgressBar from '../../components/progressBar';

class ServerConfigurationWizardRoute extends Component {

    handleSubmit = ({server_type}) => {
      const {history} = this.props;
      server_type == 1 ? (history.push(routeMap.remoteServerStep1)) : (history.push(routeMap.pollerStep1))
    }

    goToPath = path => {
      const {history} = this.props;
      history.push(path);
    };
  
    links = [
      {active: true, number: 1, path: this.goToPath.bind(this, routeMap.remoteServerStep1)},
      {active: false, number: 2}
    ];
    
   render(){
    const {links, disabled} = this;
    return (
      <div>
        <ProgressBar links={links} />
        <Form disabled={disabled} onSubmit={this.handleSubmit.bind(this)}/>
      </div>
    )
   }
}

export default ServerConfigurationWizardRoute;
