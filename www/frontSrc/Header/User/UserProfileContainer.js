import React, { Component } from 'react'
import { connect } from 'react-redux'
import UserProfile from './UserProfile'
import { getUser, getDisabledSoundNotif, getEnabledSoundNotif, putAutologin } from "../../webservices/userApi"
import 'moment-timezone'
import AutoLoginToken from '../../legacy/AutoLoginToken'

class UserProfileContainer extends Component {

  constructor(props) {
    super(props)
    this.state = {
      anchorEl: null,
      logoutUrl: 'index.php?disconnect=1',
      initial: '',
      soundNotif: null,
      token: '',
      link: location.href + '&autologin=1' + '&useralias='
    }
  }

  componentWillReceiveProps(nextProps) {
    if (this.props !== nextProps) {
      const initial = this.parseUsername(nextProps.user.fullname)
      const GeneratedToken =
        nextProps.user.autologinkey ?
          nextProps.user.autologinkey
        : AutoLoginToken.prototype.generatePassword('aKey')

      this.setState({
        initial: initial,
        soundNotif: nextProps.user.soundNotificationsEnabled,
        token: GeneratedToken
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
    const { autologinkey, userId } = this.props.user
    const input = document.getElementById("bookmarkLink")

    if (!autologinkey && autologinkey !== this.state.token) {
      this.props.autoLogin(userId, this.state.token)
    }

    input.select();
    document.execCommand("copy")
  }

  render () {
    const { user, dataFetched } = this.props
    const { anchorEl, initial, link, token } = this.state
    const open = Boolean(anchorEl)
    const buildedLink = link + user.username + '&token=' + token

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
        link={buildedLink}
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
    autoLogin: (userId, token) => {
      return dispatch(putAutologin(userId, token))
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(UserProfileContainer)