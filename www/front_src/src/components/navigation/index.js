/* eslint-disable no-undef */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-shadow */

import React, { Component } from 'react';
import { Sidebar } from '@centreon/react-components';
import { withRouter } from 'react-router-dom';
import { connect } from 'react-redux';
import { menuSelector } from '../../redux/selectors/navigation/menu';
import { reactRoutesSelector } from '../../redux/selectors/navigation/reactRoutes';
import { fetchNavigationData } from "../../redux/actions/navigationActions";

class Navigation extends Component {
  componentDidMount = () => {
    const { fetchNavigationData } = this.props;
    fetchNavigationData();
  };

  goToPage = (route) => {
    const { history } = this.props;
    history.push(route);
  };

  handleTopLevelClick = (id, { page, options }) => {
    const urlOptions = page + (options !== null ? options : '');
    this.goToPage(`/main.php?p=${urlOptions}`);
  };

  render() {
    const { navigationData, reactRoutes, history } = this.props;

    return (
      <Sidebar
        navigationData={navigationData}
        reactRoutes={reactRoutes}
        onNavigate={(_id, { url }) => {
          this.goToPage(url);
        }}
        externalHistory={history}
        handleDirectClick={this.handleTopLevelClick}
      />
    );
  }
}

const mapStateToProps = (state) => ({
  navigationData: menuSelector(state),
  reactRoutes: reactRoutesSelector(state),
});

const mapDispatchToProps = (dispatch) => {
  return {
    fetchNavigationData: () => {
      dispatch(fetchNavigationData());
    },
    updateTooltip: () => {
      dispatch(updateTooltip());
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(withRouter(Navigation));
