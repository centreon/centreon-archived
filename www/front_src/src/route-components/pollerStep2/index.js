import React, { Component } from "react";
import { Redirect } from "react-router-dom";
import Form from '../../components/forms/poller/PollerFormStepTwo';

class PollerStepTwoRoute extends Component {
    
    handleSubmit = (data) => {
        console.log(data);
    }
    
   render(){
       return (
           <Form onSubmit={this.handleSubmit.bind(this)}/>
       )
   }
    
}

export default PollerStepTwoRoute;
