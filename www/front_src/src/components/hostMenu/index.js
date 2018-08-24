import React, { Component } from 'react';
import numeral from 'numeral'

class HostMenu extends Component {

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

        const { down, warning, unreachable, ok, pending, total, url } = data;

        const { toggled } = this.state;

        return (
            <span class="wrap-middle-icons">
                <span class="wrap-middle-icon gray">
                    <span class="iconmoon icon-host" onClick={this.toggle.bind(this)}>
                        <div class={'submenu-top host' + (toggled ? ' submenu-active' : null)}>
                            <div class="submenu-top-inner">
                                <ul class="submenu-top-items">
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20202&o=h&search='} class="submenu-top-item-link">
                                            <span>All hosts</span>
                                            <span class="submenu-top-count">{total}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20202&o=h_down&search='} class="submenu-top-item-link">
                                            <span class="dot-colored red">
                                                Down hosts
                                    </span>
                                            <span class="submenu-top-count">{down.unhandled}/{down.total}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20202&o=h_unreachable&search='} class="submenu-top-item-link">
                                            <span class="dot-colored gray">
                                                Unreachable hosts
                                    </span>
                                            <span class="submenu-top-count">{unreachable.unhandled}/{unreachable.total}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20202&o=h_up&search='} class="submenu-top-item-link">
                                            <span class="dot-colored green">
                                                Ok hosts
                                    </span>
                                            <span class="submenu-top-count">{ok}</span>
                                        </a>
                                    </li>
                                    <li class="submenu-top-item">
                                        <a href={'./main.php?p=20202&o=h_pending&search='} class="submenu-top-item-link">
                                            <span class="dot-colored blue">
                                                {pending} Pending hosts
                                    </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </span>
                    {pending > 0 ? <span class="custom-icon" /> : ''}

                </span>
                {down.unhandled > 0 || unreachable.unhandled > 0 ?
                    <span class="wrap-middle-icon round round-big red">
                        <a class="number" href="#">
                            <span>{numeral(down.unhandled).format('0a')}</span>
                        </a>
                    </span> :
                    <span class="wrap-middle-icon round round-big green">
                        <a class="number" href="#">
                            <span>{numeral(ok.total).format('0a')}</span>
                        </a>
                    </span>}

                <span class="wrap-middle-icon round round-small gray-dark">
                    <a class="number" href="#">
                        <span>{numeral(unreachable.unhandled).format('0a')}</span>
                    </a>
                </span>
            </span>
        )
    }
}

export default HostMenu;
