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
import Typography from 'material-ui/Typography'
import Moment from 'moment'

const styles = theme => ({
  profileRoot: {
    display: 'flex',
    flexDirection: 'row-reverse'
  },
  moment: {
    alignSelf: 'flex-start',
    margin: '10px 0px',
  },
  headerDate: {
    fontSize: '1.5vw',
    lineHeight: '16px',
    margin: '2px 0',
  },
  headerTime: {
    fontSize: '2.5vw',
    lineHeight: '20px',
  },
  avatarButton: {
    alignSelf: 'flex-end',
    margin: '6px 4px'
  },
  avatar: {
    backgroundColor: '#FDFEFE',
    width: 36,
    height: 36,
    display: 'inline-flex',
    verticalAlign: 'middle',
    color: '#24323E',
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
    handleOpen,
    handleClose,
    anchorEl
  }) => (
  <Grid item xs={3}>
    <div className={classes.profileRoot}>
      <IconButton
        aria-haspopup="true"
        onClick={handleOpen}
        className={classes.avatarButton}
      >
        <Avatar className={classes.avatar}>
          RI
        </Avatar>
      </IconButton>
      <div className={classes.moment}>
        <div className={classes.headerDate}> {Moment(new Date()).format('LL')} </div>
        <div className={classes.headerTime}> {Moment().format('LT')} </div>
      </div>
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
          Rabaa Ridene
        </Typography>
        <Typography variant="caption" gutterBottom>
          as Rayden <a href="./main.php?p=50104&o=c" className={classes.profileLink}>Edit profile </a>
        </Typography>
      </div>
      <MenuItem onClick={handleClose}>
        <Bookmark /> Add to your bookmark
      </MenuItem>
      <MenuItem onClick={handleClose}>
        <VolumeUp /> Désactivate notification sonore
      </MenuItem>
      <div className={classes.menuFooter}>
        <a href="index.php?disconnect=1">
          <Button className={classes.logoutButton}>
            Sign out
          </Button>
        </a>
      </div>
    </Popover>
  </Grid>
    )

export default withStyles(styles)(UserProfile)
