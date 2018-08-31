import React, { Component } from "react";
import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';

class PollerStepThreeRoute extends Component {

  links = [
    {active: true, prevActive: true, number: 1, path: routeMap.serverConfigurationWizard},
    {active: true, prevActive: true, number: 2, path: routeMap.pollerStep1},
    {active: true, prevActive: true, number: 3, path: routeMap.pollerStep2},
    {active: true, number: 4},
  ];
    
  render(){
    const {links} = this;
    return (
      <div>
        <ProgressBar links={links} />
        <WizardFormInstallingStatus formTitle={'Currently installing [step in progress]'} />
      </div>
    )
  }
}

export default PollerStepThreeRoute;