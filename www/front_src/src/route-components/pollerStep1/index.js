import React, { Component } from "react";
import Form from '../../components/forms/poller/PollerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';

import {connect} from 'react-redux';

class PollerStepOneRoute extends Component {

    state = {
      error: null
    }

    handleSubmit = () => {
      const {history} = this.props;
      history.push(routeMap.pollerStep2);
    };

    goToPath = path => {
      const {history} = this.props;
      history.push(path);
    };
  
    links = [
      {active: true, number: 1, prevActive: true, path: this.goToPath.bind(this, routeMap.serverConfigurationWizard)},
      {active: true, number: 2, path: this.goToPath.bind(this, routeMap.pollerStep1)},
      {active: false, number: 3},
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

const mapStateToProps = ({pollerForm}) => ({
  pollerData: pollerForm
});

const mapDispatchToProps = {
  setPollerWizard
}
export default connect(mapStateToProps, mapDispatchToProps)(PollerStepOneRoute);
