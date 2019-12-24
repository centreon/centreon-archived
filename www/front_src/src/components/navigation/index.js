import React, { Component } from 'react';
import { Sidebar } from '@centreon/ui';
import { connect } from 'react-redux';
import { menuSelector } from '../../redux/selectors/navigation/menu';
import { reactRoutesSelector } from '../../redux/selectors/navigation/reactRoutes';
import { fetchNavigationData } from "../../redux/actions/navigationActions";

class Navigation extends Component {
  componentDidMount = () => {
    const { fetchNavigationData } = this.props;
    fetchNavigationData();
  };

  render() {
    const { navigationData, reactRoutes } = this.props;

    return (
      <Sidebar
        navigationData={navigationData}
        reactRoutes={reactRoutes}
      />
    );
  }
}

const mapStateToProps = (state) => ({
  navigationData: menuSelector(state),
  reactRoutes: reactRoutesSelector(state),
});

const mapDispatchToProps = dispatch => {
  return {
    fetchNavigationData: () => {
      dispatch(fetchNavigationData());
    },
    updateTooltip: () => {
      dispatch(updateTooltip());
    }
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(Navigation);
