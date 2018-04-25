import React from 'react'
import { withStyles } from 'material-ui/styles'
import Avatar from 'material-ui/Avatar'
import { MenuItem } from 'material-ui/Menu'
import IconButton from 'material-ui/IconButton'
import Grid from 'material-ui/Grid'
import Popover from 'material-ui/Popover'
import Button from 'material-ui/Button'
import Bookmark from 'material-ui-icons/Bookmark'
import VolumeUp from 'material-ui-icons/VolumeUp'
import VolumeMute from 'material-ui-icons/VolumeMute'
import Typography from 'material-ui/Typography'
import Clock from '../Clock/ClockContainer'

const styles = theme => ({
  profileRoot: {
    display: 'flex',
    flexDirection: 'row-reverse',
    fontFamily: theme.font.openSans,
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
  <Grid item xs={12} sm={3}>
    <div className={classes.profileRoot}>
      <IconButton
        aria-haspopup="true"
        onClick={handleOpen}
        className={classes.avatarButton}
      >
        <Avatar className={classes.avatar}>
          {initial}
        </Avatar>
      </IconButton>
      <Clock />
    </div>
    <Popover
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
          as {user.username} <a href="./main.php?p=50104&o=c" className={classes.profileLink}>Edit profile </a>
        </Typography>
      </div>

        <MenuItem onClick={handleAutologin}>
          <Bookmark className={classes.icon}/> Add to bookmark
        </MenuItem>
      <MenuItem onClick={handleNotification}>
        {soundNotif ?
          <VolumeMute className={classes.icon} />
          : <VolumeUp className={classes.icon} />
        }
        {soundNotif ? 'Disable sound notification' : 'Enable sound notification'}
      </MenuItem>
      <div className={classes.menuFooter}>
        <Button className={classes.logoutButton} href="index.php?disconnect=1">
          Sign out
        </Button>
      </div>
    </Popover>
  </Grid>
    )

export default withStyles(styles)(UserProfile)
