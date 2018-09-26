import React, { Component } from "react";
import WizardFormInstallingStatus from "../../components/wizardFormInstallingStatus";
import ProgressBar from "../../components/progressBar";
import routeMap from "../../route-maps";

import { connect } from "react-redux";
class RemoteServerStepThreeRoute extends Component {
  state = {
    generateStatus: null,
    processingStatus: null
  };

  links = [
    {
      active: true,
      prevActive: true,
      number: 1,
      path: routeMap.serverConfigurationWizard
    },
    { active: true, prevActive: true, number: 2, path: routeMap.pollerStep1 },
    { active: true, prevActive: true, number: 3, path: routeMap.pollerStep2 },
    { active: true, number: 4 }
  ];

  render() {
    const { links } = this,
          { pollerData } = this.props,
          { generateStatus, processingStatus } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <WizardFormInstallingStatus
          statusCreating={pollerData.submitStatus}
          statusGenerating={generateStatus}
          statusProcessing={processingStatus}
          formTitle={"Finalizing Setup:"}
        />
      </div>
    );
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm
});

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(
  RemoteServerStepThreeRoute
);
