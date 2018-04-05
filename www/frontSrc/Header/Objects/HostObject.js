import React from 'react'
import Button from '@material-ui/core/Button'
import { withStyles } from '@material-ui/core/styles'
import numeral from 'numeral'
import Popover from '@material-ui/core/Popover'
import Typography from '@material-ui/core/Typography'
import HostIcon from "../Icons/HostIcon"

const styles = theme => ({
  root: {
    fontFamily: theme.font.openSans,
    marginLeft: 26,
    position: 'relative'
  },
  link: {
    color: 'rgba(0, 0, 0, 0.87)',
    '&:hover': {
      color: '#09225C'
    },
    '&:visited': {
      color: 'rgba(0, 0, 0, 0.87)'
    },
    '&:active': {
      color: 'rgba(0, 0, 0, 0.87)'
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
  okStatus: {
    margin: '10px 4px',
    width: 46,
    height: 46,
    backgroundColor: theme.palette.primary.main,
    '&:hover': {
      backgroundColor: theme.palette.primary.light,
    },
    '& span': {
      fontSize: 16,
      color: '#fff',
      fontWeight: '600'
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
    '&:hover': {
      backgroundColor: theme.palette.error.light,
    },
  },
  unreachableStatus: {
    margin: '10px 4px',
    color: '#fff',
    width: 40,
    height: 40,
    '& span': {
      fontSize: 15,
      fontWeight: '600'
    },
    backgroundColor: theme.palette.unreachable.main,
    '&:hover': {
      backgroundColor: theme.palette.unreachable.light,
    },
  },
  chip: {
    height: '8px',
    width: '8px',
    borderRadius: 20,
    display: 'inline-table',
    marginRight: 6,
  },
  pendingStatus: {
    height: '12px',
    width: '12px',
    position: 'absolute',
    top: 38,
    left: 6,
    borderRadius: 20,
    border: '2px solid #E6E6E7',
    backgroundColor: theme.palette.pending.main
  },
  icon: {
    width: 34,
      height: 34,
      display: 'inline-flex',
      verticalAlign: 'middle',
      margin: '6px',
      color: '#A7A9AC',
      cursor: 'pointer',
      '&:hover': {
      color: '#D1D2D4',
    }
  },
  paper: {
    padding: theme.spacing.unit,
  },
  popover: {
    pointerEvents: 'none',
  },
  objectDetails: {
    padding: '10px 16px',
    '&:first-child' : {
      borderBottom: '1px solid #d1d2d4',
    }
  },
  total: {
    float: 'right',
    marginLeft: 34,
  },
})

const HostObject = (
  {classes, object, anchorEl, open, handleClose, handleOpen,
    down, warning, unreachable, ok, pending, total, url
  }) => (
  <div className={classes.root}>
    <HostIcon
      id='hostIcon'
      viewBox="6 156 600 600"
      className={classes.icon}
      aria-label='Hosts status'
      aria-haspopup="true"
      onClick={handleOpen}
      title="host"
    />
    { pending.total > 0 ? <span className={classes.pendingStatus} ></span> : '' }
    {down.unhandled > 0 || unreachable.unhandled > 0 ?
      <Button variant="fab" href={down.url}
              aria-label='Down hosts'
              className={(classes.status, classes.errorStatus)}>
        {numeral(down.unhandled).format('0a')}
      </Button> :
      <Button variant="fab" href={ok.url}
              aria-label='Ok hosts'
              className={(classes.status, classes.okStatus)}>
        {numeral(ok.total).format('0a')}
      </Button>
    }
    <Button variant="fab" mini href={unreachable.url}
            aria-label='Unreachable hosts'
            className={( classes.status, classes.unreachableStatus)}>
      {numeral(unreachable.unhandled).format('0a')}
    </Button>
    <Popover
      id='hostPopover'
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
        <Typography variant="body1" gutterBottom>
          <a href={url} title="all hosts list" className={classes.link}>
            All hosts
            <span className={classes.total}>{total}</span>
          </a>

        </Typography>
      </div>
      <div className={classes.objectDetails}>
        <Typography variant="body1" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#e00b3d'}}></span>
          <a href={down.url} title="hosts down" className={classes.link}>
            Down hosts
            <span className={classes.total}>{down.unhandled}/{down.total}</span>
          </a>
        </Typography>
      </div>
      <div className={classes.objectDetails}>
        <Typography variant="body1" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#818285'}}></span>
          <a href={unreachable.url} title="hosts unreachable" className={classes.link}>
            Unreachable hosts
            <span className={classes.total}>{unreachable.unhandled}/{unreachable.total}</span>
          </a>
        </Typography>
      </div>
      <div className={classes.objectDetails}>
        <Typography variant="body1" gutterBottom>
          <span className={classes.chip} style={{backgroundColor: '#88b917'}}></span>
          <a href={ok.url} title="hosts ok" className={classes.link}>Ok hosts
            <span className={classes.total}>{ok.total}</span>
          </a>
        </Typography>
      </div>
      {pending.total > 0 ?
        <div className={classes.objectDetails}>
          <Typography variant="body1" gutterBottom>
            <span className={classes.chip} style={{backgroundColor: '#2AD1D4'}}></span>
            <a href={pending.url} title="pending hosts list">
            {pending.total} Pending hosts
            </a>
          </Typography>
        </div> : ''
      }
    </Popover>
  </div>
)

export default withStyles(styles)(HostObject)
