import React, { Component } from "react";
import { Sidebar } from "@centreon/react-components";
import { withRouter } from "react-router-dom";
import { connect } from "react-redux";
import { fetchNavigationData } from "../../redux/actions/navigationActions";

class Navigation extends Component {
  componentDidMount = () => {
    const { fetchNavigationData } = this.props;
    fetchNavigationData();
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
    const { navigationData, reactRoutes } = this.props;
    return (
      <Sidebar
        navigationData={navigationData}
        reactRoutes={reactRoutes}
        onNavigate={(id, { url }) => {
          this.goToPage(url);
        }}
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
    updateTooltip: () => {
      dispatch(updateTooltip());
    }
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(withRouter(Navigation));
