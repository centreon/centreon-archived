import React from 'react'
import Button from 'material-ui/Button'
import Grid from 'material-ui/Grid'
import { withStyles } from 'material-ui/styles'
import Popover from 'material-ui/Popover'
import Typography from 'material-ui/Typography'
import PollerIcon from "../icons/PollerIcon"

const styles = theme => ({
  root: {
    position: 'relative',
    fontFamily: theme.font.openSans,
    display: 'inline-flex',
    verticalAlign: 'middle',
    margin: '6px',
  },
  'a': {
    color: '#0072CE',
    '&:hover': {
      backgroundColor: '#064166'
    },
    '&:visited': {
      color: '#10069F'
    }
  },
  status: {
    margin: '4px',
    color: '#fff',
    width: 38,
    height: 38,
    '& span': {
      fontSize: 16
    },
  },
  errorStatus: {
    margin: '10px 4px',
    width: 46,
    height: 46,
    backgroundColor: theme.palette.error.main,
    '& span': {
      fontSize: 16,
      color: '#fff',
      fontWeight: '600'
    },
  },
  chip: {
    height: '8px',
    width: '8px',
    borderRadius: 20,
    display: 'inline-table',
    marginRight: 6,
  },
  icon: {
    width: 34,
    height: 34,
    cursor: 'pointer',
  },
  paper: {
    padding: theme.spacing.unit,
  },
  popover: {
    pointerEvents: 'none',
  },
  objectDetails: {
    padding: '10px 16px',
    borderBottom: '1px solid #d1d2d4',
    '&:last-child' : {
      borderBottom: 'none',
    }
  },
  total: {
    float: 'right',
    marginLeft: 34,
  },
  errorNotif: {
    backgroundColor: theme.palette.error.lighter,
    color: theme.palette.error.main,
    padding: 10,
    borderRadius: 3,
  },
  warningNotif: {
    ackgroundColor: theme.palette.warning.light,
    color: theme.palette.warning.main,
    padding: 10,
    borderRadius: 3,
    textAlign: 'center',
  },
  primaryButton: {
    textTransform: 'initial',
    border: '1px solid ' + theme.palette.primary.dark,
    color: theme.palette.primary.main,
    '&:hover': {
      color: '#FFF',
      backgroundColor: theme.palette.primary.main,
    }
  },
})

const PollerObject = (
  {classes, iconColor, message, total, anchorEl, open, handleClose, handleOpen, className,
    database, latency, stability
  }) => (
  <div className={classes.root}>
    <PollerIcon
      id='pollerIcon'
      alt="poller icon"
      aria-haspopup="true"
      onClick={handleOpen}
      viewBox="6 156 600 600"
      className={classes.icon}
      nativeColor={iconColor}
    />
    <Popover
      id='pollerPopover'
      open={open}
      anchorEl={anchorEl}
      anchorReference='anchorEl'
      anchorPosition={{ top: 500, left: 400 }}
      onClose={handleClose}
      anchorOrigin={{
        vertical: 'bottom',
        horizontal: 'left',
      }}
      transformOrigin={{
        vertical: 'top',
        horizontal: 'left',
      }}
    >
      <div className={classes.objectDetails}>
        <Typography variant="caption" gutterBottom>
            All pollers
          <span className={classes.total}>{total}</span>
        </Typography>
      </div>
      <div className={classes.objectDetails}>
        <Typography variant="caption" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#e00b3d'}}></span>
          {stability.critical.message}
          <span className={classes.total}>{stability.critical.total}</span>
        </Typography>
        <Typography variant="caption" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#FF9A13'}}></span>
          {stability.warning.message}
          <span className={classes.total}>{stability.warning.total}</span>
        </Typography>
      </div>
      <div className={classes.objectDetails}>
        <Typography variant="caption" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#e00b3d'}}></span>
          {latency.critical.message}
          <span className={classes.total}>{latency.critical.total}</span>
        </Typography>
        <Typography variant="caption" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#FF9A13'}}></span>
          {latency.warning.message}
          <span className={classes.total}>{latency.warning.total}</span>
        </Typography>
      </div>
      <div className={classes.objectDetails}>
        <Typography variant="caption" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#e00b3d'}}></span>
          {database.critical.message}
          <span className={classes.total}>{database.critical.total}</span>
        </Typography>
        <Typography variant="caption" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#FF9A13'}}></span>
          {database.warning.message}
          <span className={classes.total}>{database.warning.total}</span>
        </Typography>
      </div>
      <div className={classes.objectDetails}>
        <Button className={classes.primaryButton} href="./main.php?p=609">
          Configure pollers
        </Button>
      </div>
    </Popover>
  </div>
)

export default withStyles(styles)(PollerObject)
