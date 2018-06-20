import React, { Component } from 'react'
import { connect } from 'react-redux'
import UserProfile from './UserProfile'
import { getUser, getDisabledSoundNotif, getEnabledSoundNotif, getaAutologin } from "../../webservices/userApi"
import 'moment-timezone'

class UserProfileContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      anchorEl: null,
      logoutUrl: 'index.php?disconnect=1',
      initial: '',
      soundNotif: null

    }
  }

  componentWillReceiveProps(nextProps) {
    if (this.props !== nextProps) {
      const initial = this.parseUsername(nextProps.user.fullname)

      this.setState({
        initial: initial,
        soundNotif: nextProps.user.soundNotificationsEnabled
      })
    }
  }

  componentDidMount = () =>  {
    this.props.getUser()
  }

  parseUsername = username => {
   return username.split("_").reduce((acc, value, index) => {

     if (index <= 1) {
       acc += value.substr(0,1).toUpperCase()
     }
     return acc
    },'')
  }

  handleOpen = event => {
    this.setState({ anchorEl: event.currentTarget })
  }

  handleClose = () => {
    this.setState({ anchorEl: null })
  }

  handleNotification = () => {
    const { soundNotif } = this.state
    const { startSonoreNotification, stopSonoreNotification } = this.props

    soundNotif === true ? stopSonoreNotification() : startSonoreNotification()

    this.setState({
      soundNotif: !soundNotif
    })
  }

  handleAutologin = () => {
    const { username, autologinkey } = this.props.user

    if (autologinkey !== '') {
      this.props.autoLogin(username, autologinkey)
    }
  }

  render () {
    const { user, dataFetched } = this.props
    const { anchorEl, initial } = this.state
    const open = Boolean(anchorEl)

    if (dataFetched) {
      this.setState({
        soundNotif: user.soundNotificationsEnabled
      })
    }

    return (
      <UserProfile
        handleClose={this.handleClose}
        handleOpen={this.handleOpen}
        handleNotification={this.handleNotification}
        handleAutologin={this.handleAutologin}
        soundNotif={this.state.soundNotif}
        initial={initial}
        user={user}
        open={open}
        anchorEl={anchorEl}
      />
    )
  }
}

const mapStateToProps = (store) => {
  return {
    user: store.user.data,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    getUser: () => {
      return dispatch(getUser())
    },
    startSonoreNotification: () => {
      return dispatch(getEnabledSoundNotif())
    },
    stopSonoreNotification: () => {
      return dispatch(getDisabledSoundNotif())
    },
    autoLogin: (username, token) => {
      return dispatch(getaAutologin(username, token))
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(UserProfileContainer)