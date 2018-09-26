import React, { Component } from "react";
import PropTypes from 'prop-types';

const getPollerStatusIcon = issues => {
  let result = (
    <React.Fragment>
      <span class="wrap-left-icon round green">
        <span class="iconmoon icon-database" />
      </span>
      <span class="wrap-left-icon round orange">
        <span class="iconmoon icon-clock" />
      </span>
      <span class="wrap-left-icon round red">
        <span class="iconmoon icon-link " />
      </span>
    </React.Fragment>
  );

  return result;
};

class PollerMenu extends Component {
  state = {
    toggled: false
  };

  constructor(props) {
    super(props);

    this.setWrapperRef = this.setWrapperRef.bind(this);
    this.handleClickOutside = this.handleClickOutside.bind(this);

    this.state = {
      toggled: false
    };
  }

  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

   ///outside click

  componentDidMount() {
    document.addEventListener('mousedown', this.handleClickOutside);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown', this.handleClickOutside);
  }

  /**
   * Set the wrapper ref
   */
  setWrapperRef(node) {
    this.wrapperRef = node;
  }

  /**
   * Alert if clicked on outside of element
   */
  handleClickOutside(event) {
    if (this.wrapperRef && !this.wrapperRef.contains(event.target)) {
      this.setState({
        toggled: false
      });
    }
  }
  ////end outside click

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
        <span ref={this.setWrapperRef}  class="toggle-submenu-arrow" onClick={this.toggle.bind(this)} >{this.props.children}</span>
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
                                <a
                                  class="submenu-top-item-link"
                                  style={{ padding: "0px 16px 17px" }}
                                >
                                  <span class={"dot-colored " + color}>
                                    {poller.name}
                                  </span>
                                </a>
                              );
                            });
                          } else return null;
                        })}
                      </li>
                    );
                  })
                : null}
              <a href={"./main.php?p=609"}>
                <button class="btn btn-big btn-green submenu-top-button">
                  Configure pollers
                </button>
              </a>
            </ul>
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

