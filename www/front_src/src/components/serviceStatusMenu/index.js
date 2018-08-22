

import React, { Component } from 'react';
import numeral from 'numeral'

class ServiceStatusMenu extends Component {

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

        if (!data || !data.total) {
            return null;
        }

        console.log(data)

        const { critical, ok, pending, total, unknown, warning } = data;

        const { toggled } = this.state;

        return (
            <div class="wrap-middle-right">
                <span class="wrap-middle-icon gray">
                    <span class="iconmoon icon-services" onClick={this.toggle.bind(this)}>
                        <div class={'submenu-top services' + (toggled ? ' submenu-active' : null)}>
                            <div class="submenu-top-inner">
                                <ul class="submenu-top-items">
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20201&o=svc&search='} class="submenu-top-item-link">
                                            <span>All services</span>
                                            <span class="submenu-top-count">{total}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20201&o=svc_critical&search='} class="submenu-top-item-link">
                                            <span class="dot-colored red">
                                                Critical services
                                    </span>
                                            <span class="submenu-top-count">{critical.unhandled}/{critical.total}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20201&o=svc_warning&search='} class="submenu-top-item-link">
                                            <span class="dot-colored blue">
                                                Warning services
                                    </span>
                                            <span class="submenu-top-count">{warning.unhandled}/{warning.total}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20201&o=svc_unknown&search='} class="submenu-top-item-link">
                                            <span class="dot-colored gray">
                                                Unknown services
                                    </span>
                                            <span class="submenu-top-count">{unknown.unhandled}/{unknown.total}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20201&o=svc_ok&search='} class="submenu-top-item-link">
                                            <span class="dot-colored green">
                                                Ok services
                                    </span>
                                            <span class="submenu-top-count">{ok}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20201&o=svc_pending&search='} class="submenu-top-item-link">
                                            <span class="dot-colored blue">
                                                {pending} Pending services
                                    </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </span>
                    { pending > 0 ? <span class="custom-icon" /> : '' }
                </span>
                {critical.unhandled == 0 && warning.unhandled == 0 && unknown.unhandled == 0 ?
                    <span class="wrap-middle-icon round round-big green" aria-label='Ok services'>
                        <a class="number" href="#">
                            <span>{numeral(ok.total).format('0a')}</span>
                        </a>
                    </span> :
                    <span class="wrap-middle-icon round round-big red" aria-label='Critical services'>
                        <a class="number" href="#">
                            <span>{numeral(critical.unhandled).format('0a')}</span>
                        </a>
                    </span>}

                <span class="wrap-middle-icon round round-small orange" aria-label='Warning services'>
                    <a class="number" href="#">
                        <span>{numeral(warning.unhandled).format('0a')}</span>
                    </a>
                </span>
                <span class="wrap-middle-icon round round-small gray-light" aria-label='Unknown services'>
                    <a class="number" href="#">
                        <span>{numeral(unknown.unhandled).format('0a')}</span>
                    </a>
                </span>
            </div>
        )
    }
}

export default ServiceStatusMenu;
