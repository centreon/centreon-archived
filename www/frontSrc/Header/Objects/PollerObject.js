import React from 'react'
import Button from '@material-ui/core/Button'
import { withStyles } from '@material-ui/core/styles'
import Popover from '@material-ui/core/Popover'
import Typography from '@material-ui/core/Typography'
import PollerIcon from "../Icons/PollerIcon"
import numeral from "numeral"

const styles = theme => ({
  root: {
    fontFamily: theme.font.openSans,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
    margin: '16px 6px',
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
  chip: {
    height: '10px',
    width: '10px',
    borderRadius: 20,
    display: 'inline-table',
    marginRight: 6,
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
  issuesDetails: {
    display: 'flex',
  },
  objectDetails: {
    padding: '10px 16px',
    maxHeight: '80px',
    overflowX: 'auto',
    borderBottom: '1px solid #d1d2d4',
  },
  bottomDetails: {
    textAlign: 'center',
    padding: '10px 16px',
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
  {classes, iconColor, total, anchorEl, open, handleClose, handleOpen,
    issues
  }) => (
  <div className={classes.root}>
    <PollerIcon
      id='pollerIcon'
      alt="poller icon"
      aria-haspopup="true"
      aria-label='Pollers status'
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
        <Typography variant="body1" gutterBottom>
            All pollers
          <span className={classes.total}>{total}</span>
        </Typography>
      </div>
      <div className={classes.issuesDetails}>
      {
        issues ?
          Object.keys(issues).map((issue, index) => {
            let message = ''

            if (issue === 'database') {
              message = 'Database updates not active'
            } else if (issue === 'stability') {
              message = 'Pollers not running'
            } else if (issue === 'latency') {
              message ='Latency detected'
            }

            console.log(message)
            return (
              <div className={classes.objectDetails} key={index}>
                <Typography variant='subheading' gutterBottom >
                  {message}
                  <span className={classes.total}>{issues[issue].total}</span>
                </Typography>
                {
                  Object.keys(issues[issue]).map((elem, index) => {
                    if (issues[issue][elem].poller) {
                      const pollers = issues[issue][elem].poller
                      return (
                        pollers.map((poller, i) => {
                          const color = elem === 'critical' ? '#e00b3d' : '#ff9a13'
                          return (
                            <Typography variant='body1' gutterBottom key={i}>
                              <span className={classes.chip} style={{backgroundColor: color}}></span>
                              {poller.name}
                            </Typography>
                          )
                        })
                      )
                    } else return null
                  })
                }
              </div>
            )
          })
        : null
      }
      </div>
      <div className={classes.bottomDetails}>
        <Button className={classes.primaryButton} aria-label='Pollers configuration' href="./main.php?p=609">
          Configure pollers
        </Button>
      </div>
    </Popover>
  </div>
)

export default withStyles(styles)(PollerObject)
