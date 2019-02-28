import React from 'react';
import {connect} from "react-redux";

const Tooltip = ({x, y, label, toggled}) => (
    <div className={`tooltip ${toggled ? ' ' : 'hidden'}`}
        style={
            {
                top:y,
                left:x
            }
        }
    >{label}</div>
)

const mapStateToProps = ({tooltip}) => (
    {
        x:tooltip.x,
        y:tooltip.y,
        label:tooltip.label,
        toggled:tooltip.toggled
    }
)

export default connect(mapStateToProps, null)(Tooltip)
