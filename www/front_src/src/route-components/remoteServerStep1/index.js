import React, { Component } from "react";
import Form from '../../components/forms/remoteServer/RemoteServerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';
import axios from "../../axios";
import {connect} from 'react-redux';

class RemoteServerStepOneRoute extends Component {
  state = {
    error: null
  }

  wizardFormApi = axios('internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer');
  wizardFormWaitListApi = axios('internal.php?object=centreon_configuration_remote&action=getWaitList');
  
  componentWillMount = () => {
    this.getWaitList = listData => {
      this.wizardFormWaitListApi.get('', listData)
        .then(response => {
          console.log(response)
        })
        .catch(err => {
          console.log(err)
      });
    }
  }

  handleSubmit = data => {
    const {history} = this.props;
    this.wizardFormApi.post('', data)
      .then(response => {
        console.log(response)
      })
      .catch(err => {
        console.log(err)
    });
    history.push(routeMap.pollerStep2);
  };

  goToPath = path => {
    const {history} = this.props;
    history.push(path);
  };

  links = [
    {active: true, number: 1, prevActive: true, path: this.goToPath.bind(this, routeMap.serverConfigurationWizard)},
    {active: true, number: 2, path: this.goToPath.bind(this, routeMap.remoteServerStep1)}
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

export default connect(mapStateToProps, mapDispatchToProps)(RemoteServerStepOneRoute);
