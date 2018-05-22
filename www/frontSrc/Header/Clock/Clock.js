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

const styles = theme => ({
  profileRoot: {
    display: 'flex',
    flexDirection: 'row-reverse',
    fontFamily: theme.font.openSans,
  },
  moment: {
    alignSelf: 'flex-start',
    margin: '10px 0px',
  },
  headerDate: {
    fontSize: 14,
  },
  headerTime: {
    fontSize: 35,
    lineHeight: '28px',
  },
})

const Clock = ({
  classes,
    currentDate,
  }) => (
      <div className={classes.moment}>
        <div className={classes.headerDate}> {currentDate.date} </div>
        <div className={classes.headerTime}> {currentDate.time} </div>
      </div>
    )

export default withStyles(styles)(Clock)
