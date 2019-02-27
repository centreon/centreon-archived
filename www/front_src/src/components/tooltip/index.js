//import React, { Component } from 'react';
import React from 'react';

//class Tooltip extends Component {
const Tooltip  = (props) => {
    let template = null;

    switch(props.type){
        case 'right':
            template = (
                <div
                    className="tooltip right"
                >
                    {props.text}
                </div>
            );
            break;
        case 'bottom':
            template = (
                <div
                    className="tooltip bottom"
                >
                    {props.text}
                </div>
            )
            break;
        default:
            template = null
    }
    return template;
}

export default Tooltip;
