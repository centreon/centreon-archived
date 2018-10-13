import React, { Component } from "react";
import PropTypes from 'prop-types';
import config from "../../config";
import axios from "../../axios";

import { connect } from "react-redux";

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

  return (
    <React.Fragment>
      <span class={`wrap-left-icon round ${databaseClass}`}>
        <span
          class="iconmoon icon-database"
          title={databaseClass === 'green' ?
            'OK: all database poller updates are active' :
            'Some database poller updates are not active; check your configuration'
          }
        />
      </span>
      <span class={`wrap-left-icon round ${latencyClass}`}>
        <span
          class="iconmoon icon-clock"
          title={latencyClass == 'green' ?
            'OK: no latency detected on your platform' :
            'Latency detected, check configuration for better optimization'
          }
        />
      </span>
    </React.Fragment>
  );
};

class PollerMenu extends Component {

  pollerService = axios(
    "internal.php?object=centreon_topcounter&action=pollersListIssues"
  );

  refreshTimeout = null;

  state = {
    toggled: false,
    data: null
  };

  UNSAFE_componentWillMount() {
    window.addEventListener('mousedown', this.handleClick, false);
    this.getData();
  };

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    clearTimeout(this.refreshTimeout);
  };

  // fetch api to get host data
  getData = () => {
    this.pollerService.get().then(({data}) => {
      this.setState({
        data
      }, this.refreshData);
    }).catch((error) => {
      if (error.response.status == 401){
        this.setState({
          data: null
        });
      }
    });
  }

  // refresh poller data every 30 seconds
  // @todo get this interval from backend
  refreshData = () => {
    clearTimeout(this.refreshTimeout);
    this.refreshTimeout = setTimeout(() => {
      this.getData();
    }, 30000);
  };

  // display/hide detailed poller data
  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

  // hide poller detailed data if click outside
  handleClick = (e) => {
    if (!this.poller || this.poller.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  render() {
    const { data, toggled } = this.state;

    if (!data) {
      return null;
    }

    // check if poller configuration page is allowed
    const { entries } = this.props.navigationData;
    const allowPollerConfiguration = entries.includes('60901')

    const statusIcon = getPollerStatusIcon(data.issues);
    return (
      <div class={"wrap-left-pollers" + (toggled ? " submenu-active" : "")}>
        <span class="wrap-left-icon" onClick={this.toggle}>
          <span class="iconmoon icon-poller" />
          <span class="wrap-left-icon__name">Pollers</span>
        </span>
        {statusIcon}
        <div ref={poller => this.poller = poller}>
          <span class="toggle-submenu-arrow" onClick={this.toggle} >{this.props.children}</span>
          <div class="submenu pollers">
            <div class="submenu-inner">
              <ul class="submenu-items list-unstyled">
                <li class="submenu-item">
                  <span class="submenu-item-link">
                    All pollers
                    <span class="submenu-count">{data.total ? data.total : "..."}</span>
                  </span>
                </li>
                {data.issues
                  ? Object.entries(data.issues).map(([key, issue]) => {
                    let message = "";

                    if (key === "database") {
                      message = "Database updates not active";
                    } else if (key === "stability") {
                      message = "Pollers not running";
                    } else if (key === "latency") {
                      message = "Latency detected";
                    }

                    return (
                      <li class="submenu-top-item">
                        <span class="submenu-top-item-link">
                          {message}
                          <span class="submenu-top-count">
                            {issue.total ? issue.total : "..."}
                          </span>
                        </span>
                        {Object.entries(issue).map(([elem, values]) => {
                          if (values.poller) {
                            const pollers = values.poller;
                            return pollers.map((poller) => {
                              let color = 'red';
                              if (elem === 'warning') {
                                color = 'orange';
                              }
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
                {allowPollerConfiguration && /* display poller configuration button if user is allowed */
                  <a href={config.urlBase + "main.php?p=609"}>
                    <button
                      onClick={this.toggle}
                      class="btn btn-big btn-green submenu-top-button"
                    >
                      Configure pollers
                    </button>
                  </a>
                }
              </ul>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

const mapStateToProps = ({ navigation }) => ({
  navigationData: navigation
});

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(PollerMenu);

PollerMenu.propTypes = {
  children: PropTypes.element.isRequired,
};

