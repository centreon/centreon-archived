import React, {Component} from 'react'
import AppBar from '@material-ui/core/AppBar'
import grey from '@material-ui/core/colors/grey'
import { withStyles } from '@material-ui/core/styles'
import PropTypes from 'prop-types'
import ServiceObject from './Objects/ServiceObjectContainer'
import HostObject from './Objects/HostObjectContainer'
import PollerObject from './Objects/PollerObjectContainer'
import UserProfile from './User/UserProfileContainer'
import Logo from './Logo/LogoContainer'
import Clock from './Clock/ClockContainer'
import Nav from './Nav/NavContainer'


const styles = theme => ({
  root: {
    flexGrow: 1,
    zIndex: 1,
    fontFamily: theme.font.openSans,
    overflow: 'hidden',
    marginBottom: 40
  },
  container: {
    display: 'grid',
    gridTemplateColumns: 'repeat(12, 1fr)',
    gridGap: `${theme.spacing.unit * 3}px`,
  },
  clockUserContainer: {
    display: 'flex',
    justifyContent: 'flex-end',
  },
  appBar: {
    zIndex: theme.zIndex.drawer + 1,
    color: '#fff',
    backgroundColor: '#E7E7E8',
  },
  objectContainer: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
  },
  avatar: {
    margin: 10,
    color: '#fff',
    backgroundColor: grey[200],
  },
})

class TopHeader extends Component {
  render () {
    const {classes} = this.props
    return (
      <div className={classes.root}>
        <AppBar position="static" className={classes.appBar}>
          <div className={classes.container}>
            <div style={{ gridColumnEnd: 'span 2' }}>
              <Logo />
            </div>
            <div style={{ gridColumnEnd: 'span 3' }}>
              <Nav />
            </div>
            <div style={{ gridColumnEnd: 'span 1' }}>
              <PollerObject />
            </div>
            <div style={{ gridColumnEnd: 'span 2' }}>
              <HostObject />
            </div>
            <div style={{ gridColumnEnd: 'span 2' }}>
              <ServiceObject />
            </div>
            <div style={{ gridColumnEnd: 'span 2' }}>
              <div className={classes.clockUserContainer}>
                <Clock />
                <UserProfile />
              </div>
            </div>
          </div>
        </AppBar>
      </div>
    )
  }
}

TopHeader.propTypes = {
  classes: PropTypes.object.isRequired,
}

export default withStyles(styles)(TopHeader)
