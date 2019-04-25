import React, { Component } from "react";
import { connect } from "react-redux";
import { Switch, Route } from "react-router-dom";
import { history } from "../../store";
import { reactRoutes } from "../../route-maps";
import ReactRoute from '../router/reactRoute';
import NotAllowedPage from '../../route-components/notAllowedPage';

// class to manage internal react pages
class InternalRouter extends Component {

  shouldComponentUpdate(nextProps) {
    const { acl } = this.props;
    return (acl.loaded === false && nextProps.acl.loaded === true);
  }

  render() {
    const { acl } = this.props;

    if (!acl.loaded) {
      return null;
    }

    return (
      <>
        {reactRoutes.map(({ path, comp, ...rest }) => (
          <ReactRoute
            history={history}
            path={path}
            component={acl.routes.includes(path) ? comp : NotAllowedPage}
            {...rest}
          />
        ))}
      </>
    );
  };
}

const mapStateToProps = ({ navigation }) => ({
  acl: navigation.acl,
});

export default connect(mapStateToProps)(InternalRouter);
