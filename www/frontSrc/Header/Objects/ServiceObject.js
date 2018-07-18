import React from 'react'
import Button from '@material-ui/core/Button'
import Grid from '@material-ui/core/Grid'
import { withStyles } from '@material-ui/core/styles'
import numeral from 'numeral'
import Popover from '@material-ui/core/Popover'
import Typography from '@material-ui/core/Typography'
import ServiceIcon from "../icons/ServiceIcon"

const styles = theme => ({
  root: {
    fontFamily: theme.font.openSans,
    margin: '0 12px',
    '&:before': {
      width: 1,
      height: 30,
      backgroundColor: '#d1d2d4',
      content: '""',
      position: 'absolute',
      top: 18,
      margin: '0px -10px',
    },
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
    '&:hover': {
      backgroundColor: theme.palette.error.light,
    },
    '& span': {
      fontSize: 16,
      color: '#fff',
      fontWeight: '600'
    },

  },
  warningStatus: {
    margin: '10px 4px',
    color: '#fff',
    width: 40,
    height: 40,
    '& span': {
      fontSize: 16
    },
    backgroundColor: theme.palette.warning.main,
    '&:hover': {
      backgroundColor: theme.palette.warning.light,
    },
  },
  unknownStatus: {
    margin: '10px 4px',
    color: '#fff',
    width: 40,
    height: 40,
    '& span': {
      fontSize: 15,
      fontWeight: '600'
    },
    backgroundColor: theme.palette.unknown.main,
    '&:hover': {
      backgroundColor: theme.palette.unknown.dark,
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
    borderBottom: '1px solid #d1d2d4',
    '&:last-child' : {
      borderBottom: 'none',
    }
  },
  total: {
    float: 'right',
    marginLeft: 34,
  },
})

const ServiceObject = ({
    classes, object, anchorEl, open, handleClose, handleOpen,
    critical, warning, unknown, ok, pending, total, url
}) => (
  <div className={classes.root}>
      <ServiceIcon
        viewBox="284 -284 600 600"
        className={classes.icon}
        aria-haspopup="true"
        onClick={handleOpen}
      />
      <Button variant="fab" href={critical.url}
              className={(classes.status, classes.errorStatus)}>
        {numeral(critical.unhandled).format('0a')}
      </Button>
      <Button variant="fab" mini  href={warning.url}
              className={( classes.status, classes.warningStatus)}>
        {numeral(warning.unhandled).format('0a')}
      </Button>
      <Button variant="fab" mini color="primary"  href={unknown.url}
              className={(classes.status, classes.unknownStatus)}>
        {numeral(unknown.unhandled).format('0a')}
      </Button>

      <Popover
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
            <a href={url} title="all services list">
              All services
            </a>
            <span className={classes.total}>{total}</span>
          </Typography>
        </div>
        <div className={classes.objectDetails}>
          <Typography variant="caption" gutterBottom>
            <span className={classes.chip} style={{backgroundColor: '#e00b3d'}}></span>
            <a href={critical.url} title="services critical list">
              {critical.unhandled} Unhandled problems
            </a>
            <span className={classes.total}> / {critical.total}</span>
          </Typography>
        </div>
        <div className={classes.objectDetails}>
          <Typography variant="caption" gutterBottom>
            <span className={classes.chip} style={{backgroundColor: '#FF9A13'}}></span>
            <a href={warning.url} title="services warning list">
              {warning.unhandled} Warning services
            </a>
            <span className={classes.total}> / {warning.total}</span>
          </Typography>
        </div>
        <div className={classes.objectDetails}>
          <Typography variant="caption" gutterBottom>
            <span className={classes.chip} style={{backgroundColor: '#bcbdc0'}}></span>
            <a href={unknown.url} title="services unknown list">
              {unknown.unhandled} Unknown services
            </a>
            <span className={classes.total}> / {unknown.total}</span>
          </Typography>
        </div>
        <div className={classes.objectDetails}>
          <Typography variant="caption" gutterBottom>
            <span className={classes.chip} style={{backgroundColor: '#88b917'}}></span>
            <a href={ok.url} title="services ok list">
              {ok.total} Ok services
            </a>
          </Typography>
        </div>
        {pending.total > 0 ?
          <div className={classes.objectDetails}>
            <Typography variant="caption" gutterBottom>
              <span className={classes.chip} style={{backgroundColor: '#2AD1D4'}}></span>
              <a htef={pending.url} title="pending services list">
                {ok.total} Pending services
              </a>
            </Typography>
          </div> : ''
        }
      </Popover>
  </div>
)

export default withStyles(styles)(ServiceObject)
