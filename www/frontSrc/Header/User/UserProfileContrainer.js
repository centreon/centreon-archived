import React, { Component } from 'react'
import { connect } from 'react-redux'
import UserProfile from './UserProfile'
import { getUser } from "../../webservices/userApi"

class UserProfileContrainer extends Component {

  state = {
    anchorEl: null,
    logoutUrl: 'index.php?disconnect=1'
  };

  handleOpen = event => {
    this.setState({ anchorEl: event.currentTarget })
  };

  handleClose = () => {
    this.setState({ anchorEl: null })
  };

  render () {

    console.log(this.props.user)

    const { anchorEl } = this.state
    const open = Boolean(anchorEl)

    return (
      <UserProfile
        handleClose={this.handleClose}
        handleOpen={this.handleOpen}
        open={open}
        anchorEl={anchorEl}
      />
      )
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    user: () => {
      dispatch(getUser())
    }
  }
}

export default connect(null, mapDispatchToProps)(UserProfileContrainer)
