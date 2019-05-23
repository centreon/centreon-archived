import React, { Component } from "react";
import { Sidebar } from "@centreon/react-components";
import { withRouter } from "react-router-dom";
import { connect } from "react-redux";
import { fetchNavigationData, fetchReactRoutesData } from "../../redux/actions/navigationActions";

class Navigation extends Component {
  componentDidMount = () => {
    const { fetchNavigationData, fetchReactRoutesData } = this.props;
    fetchNavigationData();
    fetchReactRoutesData();
  };

  goToPage = route => {
    const { history } = this.props;
    history.push(route);
  };

  handleTopLevelClick = (id, { page, options }) => {
    const urlOptions = page + (options !== null ? options : "");
    this.goToPage(`/main.php?p=${urlOptions}`);
  };

  render() {
    const { navigationData, reactRoutes, history } = this.props;
    return (
      <Sidebar
        navigationData={navigationData}
        reactRoutes={reactRoutes}
        onNavigate={(id, { url }) => {
          this.goToPage(url);
        }}
        externalHistory={history}
        handleDirectClick={this.handleTopLevelClick}
      />
    );
  }
}

const mapStateToProps = ({ navigation }) => ({
  navigationData: navigation.entries,
  reactRoutes: navigation.reactRoutes
});

const mapDispatchToProps = dispatch => {
  return {
    fetchNavigationData: () => {
      dispatch(fetchNavigationData());
    },
    fetchReactRoutesData: () => {
      dispatch(fetchReactRoutesData());
    },
    updateTooltip: () => {
      dispatch(updateTooltip());
    }
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(withRouter(Navigation));
