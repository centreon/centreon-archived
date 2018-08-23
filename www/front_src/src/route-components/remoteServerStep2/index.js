import React, { Component } from "react";
import { Redirect } from "react-router-dom";
import Form from '../../components/forms/remoteServer/RemoteServerFormStepTwo';

class RemoteServerStepTwoRoute extends Component {
    
    handleSubmit = (data) => {
        console.log(data);
    }
    
   render(){
       return (
           <Form onSubmit={this.handleSubmit.bind(this)}/>
       )
   }
    
}

export default RemoteServerStepTwoRoute;
