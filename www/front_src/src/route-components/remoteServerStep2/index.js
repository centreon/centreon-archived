import React, { Component } from "react";
import Form from '../../components/forms/remoteServer/RemoteServerFormStepTwo';
import routeMap from '../../route-maps';
import ProgressBar from '../../components/progressBar';

class RemoteServerStepTwoRoute extends Component {
  handleSubmit = (data) => {
    console.log(data);
  }

  goToPath = path => {
    const {history} = this.props;
    history.push(path);
  };
    
  links = [
    {active: true, prevActive: true, number: 1, path: this.goToPath.bind(this, routeMap.serverConfigurationWizard)},
    {active: true, prevActive: true, number: 2, path: this.goToPath.bind(this, routeMap.remoteServerStep1)},
    {active: true, number: 3}
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

export default RemoteServerStepTwoRoute;
