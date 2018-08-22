import React, { Component } from 'react'


class Clock extends Component {

    render() {
        const { clockData } = this.props;
        return (
            <div class="wrap-right-date">
                <span class="wrap-right-date">{clockData.date}</span>
                <span class="wrap-right-time">{clockData.time}</span>
            </div>
        )
    }
}

export default Clock;