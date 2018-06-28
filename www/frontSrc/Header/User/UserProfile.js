import React from 'react'
import { withStyles } from '@material-ui/core/styles'
import Avatar from '@material-ui/core/Avatar'
import MenuItem from '@material-ui/core/MenuItem'
import IconButton from '@material-ui/core/IconButton'
import Popover from '@material-ui/core/Popover'
import Button from '@material-ui/core/Button'
import Typography from '@material-ui/core/Typography'
import Tooltip from '@material-ui/core/Tooltip'
import TextField from '@material-ui/core/TextField'

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
  Clipboard: {
    padding : '8px 16px'
  },
  copyButton: {
    borderBottomLeftRadius: 0,
    borderTopLeftRadius: 0,
    margin: '-1px',
    borderBottom: '2px solid',
    padding: '5px 8px',
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
    link,
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
      <Tooltip id="tooltip-top-start" title="Copy to add to your bookmark" placement="top">
      <div className={classes.Clipboard}>
          <TextField id="bookmarkLink" defaultValue={link} />
          <Button variant="outlined" size="small" color="secondary" onClick={handleAutologin} className={classes.copyButton}>
            Copy
          </Button>
      </div>
      </Tooltip>
      <div className={classes.menuFooter}>
        <Button className={classes.logoutButton} size="small" aria-label='Logout' href="index.php?disconnect=1">
          Sign out
        </Button>
      </div>
    </Popover>
  </div>
    )

export default withStyles(styles)(UserProfile)
