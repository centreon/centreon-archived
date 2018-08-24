import React, { Component } from "react";
import { Redirect } from "react-router-dom";
import Form from '../../components/forms/ServerConfigurationWizardForm';
import routeMap from '../../route-maps';

class ServerConfigurationWizardRoute extends Component {
    
    handleSubmit = ({server_type}) => {
      const {history} = this.props;
      if(server_type == 1) {
        history.push(routeMap.remoteServerStep1)
      } else {
        history.push(routeMap.pollerStep1)
      }
    }
    
   render(){
    return (
      <Form onSubmit={this.handleSubmit.bind(this)}/>
    )
   }
}

export default ServerConfigurationWizardRoute;
