/* eslint-disable no-undef */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-shadow */

import React, { Component } from 'react';
import { Sidebar } from '@centreon/ui';
import { connect } from 'react-redux';
import { menuSelector } from '../../redux/selectors/navigation/menu';
import { reactRoutesSelector } from '../../redux/selectors/navigation/reactRoutes';
import { fetchNavigationData } from '../../redux/actions/navigationActions';

interface Props {
  fetchNavigationData: Function;
  navigationData: Array;
  reactRoutes: object;
}

class Navigation extends Component<PRops> {
  public componentDidMount = () => {
    const { fetchNavigationData } = this.props;
    fetchNavigationData();
  };

  public render() {
    const { navigationData, reactRoutes } = this.props;

    return (
      <Sidebar navigationData={navigationData} reactRoutes={reactRoutes} />
    );
  }
}

const mapStateToProps = (state: object) => ({
  navigationData: menuSelector(state),
  reactRoutes: reactRoutesSelector(state),
});

const mapDispatchToProps = (dispatch: Function) => {
  return {
    fetchNavigationData: () => {
      dispatch(fetchNavigationData());
    },
    updateTooltip: () => {
      dispatch(updateTooltip());
    },
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(Navigation);
