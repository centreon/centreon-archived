import React, { Component } from 'react';

const getPollerStatusClass = (issues) => {
    let result = '';

    if (issues && issues.length != 0) {
        for (let key in issues) {
            if (issues[key].warning) {
                result = 'warning'
            }
            if (issues[key].critical) {
                result = 'critical'
            }
        }
    }

    return result
}

class PollerMenu extends Component {

    state = {
        toggled: false
    }

    toggle = () => {
        const { toggled } = this.state;
        this.setState({
            toggled: !toggled
        })
    }

    render() {
        const { data } = this.props;

        if (!data) {
            return null;
        }

        const { total, issues } = data;
        const { toggled } = this.state;

        const statusClass = getPollerStatusClass(issues);

        return (
            <span class="wrap-middle-icon">
                <span class={'iconmoon icon-poller ' + statusClass} onClick={this.toggle.bind(this)}>
                    <div class={'submenu-top configure' + (toggled ? ' submenu-active' : null)}>
                        <div class="submenu-top-inner">
                            <ul class="submenu-top-items">
                                <li class="submenu-top-item">
                                    <span class="submenu-top-item-link">
                                        All pollers
                                        <span class="submenu-top-count">{total ? total : '...'}</span>
                                    </span>
                                </li>
                                {
                                    issues ?
                                        Object.keys(issues).map((issue, index) => {
                                            let message = ''

                                            if (issue === 'database') {
                                                message = 'Database updates not active'
                                            } else if (issue === 'stability') {
                                                message = 'Pollers not running'
                                            } else if (issue === 'latency') {
                                                message = 'Latency detected'
                                            }

                                            return (
                                                <li class="submenu-top-item">
                                                    <span class="submenu-top-item-link">
                                                        {message}
                                                        <span class="submenu-top-count">{issues[issue].total ? issues[issue].total : '...'}</span>
                                                    </span>
                                                    {
                                                        Object.keys(issues[issue]).map((elem, index) => {
                                                            if (issues[issue][elem].poller) {
                                                                const pollers = issues[issue][elem].poller
                                                                return (
                                                                    pollers.map((poller, i) => {
                                                                        const color = elem === 'critical' ? 'red' : 'blue'
                                                                        return (
                                                                            <a class="submenu-top-item-link" style={{ padding: '0px 16px 17px' }}>
                                                                                <span class={'dot-colored ' + color}>
                                                                                    {poller.name}
                                                                                </span>
                                                                            </a>
                                                                        )
                                                                    })
                                                                )
                                                            } else return null
                                                        })
                                                    }
                                                </li>
                                            )
                                        })
                                        : null
                                }

                                <a href={'./main.php?p=609'}>
                                    <button class="btn btn-big btn-green submenu-top-button" type="button">
                                        Configure pollers
                                    </button>
                                </a>
                            </ul>
                        </div>
                    </div>
                </span>
            </span>
        )
    }
}

export default PollerMenu;
