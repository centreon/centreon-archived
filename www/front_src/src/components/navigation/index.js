import React, {Component} from 'react';
import { Sidebar } from '@centreon/react-components';
import { connect } from "react-redux";
import { fetchNavigationData } from "../../redux/actions/navigationActions";

class Navigation extends Component {

  componentDidMount = () => {
    const { fetchNavigationData } = this.props;
    fetchNavigationData();
  };

  render() { 
    const {navigationData} = this.props;
    return ( 
      <Sidebar navigationData={navigationData} />
    );
  }
}
 
const mapStateToProps = ({ navigation }) => ({
  navigationData: navigation.entries
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

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(Navigation));