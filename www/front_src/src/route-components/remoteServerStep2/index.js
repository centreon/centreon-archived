import React, { Component } from "react";
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
