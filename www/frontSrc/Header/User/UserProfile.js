import React from 'react'
import { withStyles } from '@material-ui/core/styles'
import Avatar from '@material-ui/core/Avatar'
import MenuItem from '@material-ui/core/MenuItem'
import IconButton from '@material-ui/core/IconButton'
import Popover from '@material-ui/core/Popover'
import Button from '@material-ui/core/Button'
import Bookmark from '@material-ui/icons/Bookmark'
import VolumeUp from '@material-ui/icons/VolumeUp'
import VolumeMute from '@material-ui/icons/VolumeMute'
import Typography from '@material-ui/core/Typography'

const styles = theme => ({
  profileRoot: {
    alignSelf: 'center',
    margin: '10px 0px',
  },
  avatarButton: {
    alignSelf: 'center',
    margin: '0px 6px'
  },
  avatar: {
    backgroundColor: '#FDFEFE',
    width: 38,
    height: 38,
    display: 'inline-flex',
    verticalAlign: 'middle',
    color: '#24323E',
  },
  icon: {
    margin: '0 4px 0px 0px',
    fill: '#76777f',
  },
  profile: {
    padding: '10px 16px',
    borderBottom: '1px solid #d1d2d4',
  },
  profileLink: {
    float: 'right'
  },
  logoutButton: {
    textTransform: 'initial',
    border: '1px solid ' + theme.palette.error.main,
    color: theme.palette.error.main,
    '&:hover': {
      color: '#FFF',
      backgroundColor: theme.palette.error.main,
    }
  },
  menuFooter: {
    display: 'flex',
    flexDirection: 'row-reverse',
    padding: '10px 16px',
    boxSizing: 'content-box',
    borderTop: '1px solid #d1d2d4',
  }
})

const UserProfile = ({
  classes,
    open,
    user,
    initial,
    currentDate,
    handleOpen,
    handleClose,
    handleNotification,
    handleAutologin,
    soundNotif,
    anchorEl
  }) => (
  <div className={classes.profileRoot}>
      <IconButton
        aria-haspopup="true"
        onClick={handleOpen}
        className={classes.avatarButton}
        id="userIcon"
        aria-label='User Profile'
      >
        <Avatar className={classes.avatar}>
          {initial}
        </Avatar>
      </IconButton>
    <Popover
      id="userPopover"
      open={open}
      anchorEl={anchorEl}
      anchorReference='anchorEl'
      anchorPosition={{ top: 200, left: 400 }}
      onClose={handleClose}
      anchorOrigin={{
        vertical: 'bottom',
        horizontal: 'right',
      }}
      transformOrigin={{
        vertical: 'top',
        horizontal: 'right',
      }}
    >
      <div className={classes.profile}>
        <Typography component="title" gutterBottom>
          {user.fullname}
        </Typography>
        <Typography variant="caption" gutterBottom>
          as {user.username} <a href="./main.php?p=50104&o=c" aria-label='Edit profile' className={classes.profileLink}>Edit profile </a>
        </Typography>
      </div>

        <MenuItem onClick={handleAutologin} id='autologinAction'>
          <Bookmark className={classes.icon}/> Add to bookmark
        </MenuItem>
      <MenuItem onClick={handleNotification} id='notifAction'>
        <VolumeUp className={classes.icon} />
        {soundNotif ? 'Disable sound notification' : 'Enable sound notification'}
      </MenuItem>
      <div className={classes.menuFooter}>
        <Button className={classes.logoutButton} aria-label='Logout' href="index.php?disconnect=1">
          Sign out
        </Button>
      </div>
    </Popover>
  </div>
    )

export default withStyles(styles)(UserProfile)
