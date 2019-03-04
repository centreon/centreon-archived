import React, { Component, Suspense } from "react";
import { connect } from "react-redux";

import { dynamicImport } from "../../utils/dynamicImport";

import { setRefreshIntervals } from '../../redux/actions/refreshActions';

import PollerMenu from "../pollerMenu";
import UserMenu from "../userMenu";
import HostMenu from "../hostMenu";
import ServiceStatusMenu from "../serviceStatusMenu";

import axios from '../../axios'

class TopHeader extends Component {

  refreshIntervalsApi = axios("internal.php?object=centreon_topcounter&action=refreshIntervals");

  constructor(props) {
    super(props);

    const rootUrl = window.location.pathname.split('/')[1];
    const LoadableComponent = React.lazy(
      () => dynamicImport('/' + rootUrl + '/modules/centreon-bam-server/hooks/hook.js')
    );

    this.state = {
      LoadableComponent: LoadableComponent
    };
  }


  getRefreshIntervals = () => {
    const { setRefreshIntervals } = this.props;
    this.refreshIntervalsApi
      .get()
      .then(({ data }) => {
        setRefreshIntervals(data);
      })
      .catch((err) => {
        console.log(err);
      });
  }

  UNSAFE_componentWillMount = () => {
    this.getRefreshIntervals()
  }

  render() {
    const {LoadableComponent} = this.state;

    return (
      <header class="header">
        <div class="header-icons">
          <div class="wrap wrap-left">
            <PollerMenu />
          </div>
          <div class="wrap wrap-right">
            <Suspense fallback="Loading...">
              <LoadableComponent/>
            </Suspense>
            <HostMenu />
            <ServiceStatusMenu />
            <UserMenu />
          </div>
        </div>
      </header>
    );
  };
}

const mapStateToProps = () => ({});

const mapDispatchToProps = {
  setRefreshIntervals
};

export default connect(mapStateToProps, mapDispatchToProps)(TopHeader);
