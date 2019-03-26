import React, { Component } from "react";
import Clock from "../clock";
import config from "../../config";
import {Translate} from 'react-redux-i18n';
import axios from "../../axios";
import { connect } from "react-redux";
import { Link } from "react-router-dom";

const EDIT_PROFILE_TOPOLOGY_PAGE = '50104'

class UserMenu extends Component {

  userService = axios("internal.php?object=centreon_topcounter&action=user");

  refreshTimeout = null;

  state = {
    toggled: false,
    copied: false,
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

  // fetch api to get user data
  getData = () => {
    this.userService.get().then(({data}) => {
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

  // refresh user data every minutes
  // @todo get this interval from backend
  refreshData = () => {
    clearTimeout(this.refreshTimeout);
    this.refreshTimeout = setTimeout(() => {
      this.getData();
    }, 60000);
  };

  toggle = () => {
    const { toggled } = this.state;
    this.setState({
      toggled: !toggled
    });
  };

  //copy for autologin link
  onCopy = () => {
    this.autologinNode.select();
    window.document.execCommand('copy');
    this.setState({
      copied: true,
      toggled: false
    });
  };

  handleClick = (e) => {
    if (!this.profile || this.profile.contains(e.target)) {
      return;
    }
    this.setState({
      toggled: false
    });
  };

  render() {
    const { data, toggled, copied } = this.state;

    if (!data) {
      return null;
    }

    // check if edit profile page (My Account) is allowed
    const { entries } = this.props.navigationData;
    const allowEditProfile = entries.includes(EDIT_PROFILE_TOPOLOGY_PAGE)

    const { fullname, username, autologinkey } = data;

    //creating autologin link, getting href, testing if there is a parameter, then generating link : if '?' then &autologin(etc.)
    const gethref = window.location.href,
          conditionnedhref = gethref + (window.location.search ? '&' : '?'),
          autolink = conditionnedhref + 'autologin=1&useralias=' + username + '&token=' + autologinkey

    return (
      <div class={"wrap-right-user" + (toggled ? " submenu-active" : "")}>
        <Clock/>
        <div ref={profile => this.profile = profile}>
          <span class="iconmoon icon-user" onClick={this.toggle} />
          <div class={"submenu profile"}>
            <div class="submenu-inner">
              <ul class="submenu-items list-unstyled">
                <li class="submenu-item">
                  <span class="submenu-item-link">
                    <span class="submenu-user-name">{fullname}</span>
                    <span class="submenu-user-type"><Translate value="as"/> {username}</span>
                    {allowEditProfile &&
                      <Link
                        to={config.urlBase + "main.php?p=" + EDIT_PROFILE_TOPOLOGY_PAGE + "&o=c"}
                        class="submenu-user-edit"
                        onClick={this.toggle}
                      >
                        <Translate value="Edit profile"/>
                      </Link>
                    }
                  </span>
                </li>
                {autologinkey &&
                  <>
                    <button
                      className={'submenu-user-button'}
                      onClick={this.onCopy}
                    >
                      <Translate value="Copy autologin link"/>
                      <span className={"btn-logout-icon icon-copy " + (copied && ["icon-copied"])}></span>
                    </button>
                    <textarea
                      id="autologin-input"
                      className={'hidden-input'}
                      ref={node => this.autologinNode = node}
                      value={autolink}
                    />
                  </>
                }
              </ul>
              <div class="button-wrap">
                <a href={config.urlBase + "index.php?disconnect=1"}>
                  <button class="btn btn-small logout"><Translate value="Logout"/></button>
                </a>
              </div>
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

export default connect(mapStateToProps, mapDispatchToProps)(UserMenu);