import React, { Component } from "react";
import PropTypes from 'prop-types';
import config from "../../config";

const getIssueClass = (issues, key) => {
  return (issues && issues.length != 0) ?
  ((issues[key]) ?
    ((issues[key].warning) ?
      'orange'
      : ((issues[key].critical)
        ? 'red'
        : 'green'))
    : 'green')
  : 'green';
}

const getPollerStatusIcon = issues => {

  let databaseClass = getIssueClass(issues, 'database');

  let latencyClass = getIssueClass(issues, 'latency');

  let result = (
    <React.Fragment>
      <span class={`wrap-left-icon round ${databaseClass}`}>
        <span class="iconmoon icon-database" title={databaseClass === 'green' ? 'OK: all database poller updates are active' : 'Some database poller updates are not active; check your configuration' } />
      </span>
      <span class={`wrap-left-icon round ${latencyClass}`}>
        <span class="iconmoon icon-clock" title={latencyClass == 'green' ? 'OK: no latency detected on your platform' : 'Latency detected, check configuration for better optimization' } />
      </span>
    </React.Fragment>
  );

  return result;
};

class PollerMenu extends Component {
  state = {
    toggled: false
  };

  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

  UNSAFE_componentWillMount() {
    window.addEventListener('mousedown', this.handleClick, false);
  };

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
  };

  handleClick = (e) => {
    if (this.poller.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  render() {
    const { data } = this.props;

    if (!data) {
      return null;
    }

    const { total, issues } = data;
    const { toggled } = this.state;

    const statusIcon = getPollerStatusIcon(issues);
    return (
      <div class={"wrap-left-pollers" + (toggled ? " submenu-active" : "")}>
        <span class="wrap-left-icon" onClick={this.toggle.bind(this)}>
          <span class="iconmoon icon-poller" />
          <span class="wrap-left-icon__name">Pollers</span>
        </span>
        {statusIcon}
        <div ref={poller => this.poller = poller}>
          <span class="toggle-submenu-arrow" onClick={this.toggle.bind(this)} >{this.props.children}</span>
          <div class="submenu pollers">
            <div class="submenu-inner">
              <ul class="submenu-items list-unstyled">
                <li class="submenu-item">
                  <span class="submenu-item-link">
                    All pollers
                    <span class="submenu-count">{total ? total : "..."}</span>
                  </span>
                </li>
                {issues
                  ? Object.keys(issues).map((issue, index) => {
                    let message = "";

                    if (issue === "database") {
                      message = "Database updates not active";
                    } else if (issue === "stability") {
                      message = "Pollers not running";
                    } else if (issue === "latency") {
                      message = "Latency detected";
                    }

                    return (
                      <li class="submenu-top-item">
                        <span class="submenu-top-item-link">
                          {message}
                          <span class="submenu-top-count">
                            {issues[issue].total ? issues[issue].total : "..."}
                          </span>
                        </span>
                        {Object.keys(issues[issue]).map((elem, index) => {
                          if (issues[issue][elem].poller) {
                            const pollers = issues[issue][elem].poller;
                            return pollers.map((poller, i) => {
                              const color =
                                elem === "critical" ? "red" : "blue";
                              return (
                                <span
                                  class="submenu-top-item-link"
                                  style={{ padding: "0px 16px 17px" }}
                                >
                                  <span class={"dot-colored " + color}>
                                    {poller.name}
                                  </span>
                                </span>
                              );
                            });
                          } else return null;
                        })}
                      </li>
                    );
                  })
                  : null}
                <a href={config.urlBase + "main.php?p=609"}>
                  <button 
                    onClick={this.toggle}
                    class="btn btn-big btn-green submenu-top-button">
                    Configure pollers
                  </button>
                </a>
              </ul>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default PollerMenu;

PollerMenu.propTypes = {
  children: PropTypes.element.isRequired,
};

