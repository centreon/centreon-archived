import React, { Component } from "react";
import { Redirect } from "react-router-dom";
import Form from '../../components/forms/poller/PollerFormStepOne';

import { setPollerWizard } from '../../redux/actions/pollerWizardActions';

import {connect} from 'react-redux';
import axios from "../../axios";



class PollerStepOneRoute extends Component {

    state = {
      error: null
    }

    wizardFormApi = axios('internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer');

    handleSubmit = (data) => {
      // console.log(data)
      this.wizardFormApi.post(data)
        .then(res => console.log(res.data))
        .catch(err => console.log(err))

    }
    
   render(){
       return (
           <Form onSubmit={this.handleSubmit.bind(this)}/>
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
