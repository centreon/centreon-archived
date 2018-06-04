import React from 'react'
import { withStyles } from '@material-ui/core/styles'
import Tabs from '@material-ui/core/Tabs'
import Tab from '@material-ui/core/Tab'
import HomeIcon from "../Icons/HomeIcon"
import MonitoringIcon from "../Icons/MonitoringIcon"
import ConfigurationIcon from "../Icons/ConfigurationIcon"
import AdministrationIcon from "../Icons/AdministrationIcon"
import ReportingIcon from "../Icons/ReportingIcon"
import SecondLevel from "./SecondLevel"

const styles = theme => ({
  root: {
   /* display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',*/
  },
  tabsRoot: {
    borderBottom: '1px solid #e8e8e8',
  },
  tabsIndicator: {
    backgroundColor: '#1890ff',
  },
  tabRoot: {
    textTransform: 'initial',
    minWidth: 48,
    borderRadius: 48,
    '&:hover': {
      color: '#40a9ff',
      opacity: 1,
    },
    '&$tabSelected': {
      color: '#1890ff',
      fontWeight: theme.typography.fontWeightMedium,
    },
    '&:focus': {
      color: '#40a9ff',
    },
  },
  tabSelected: {},
  wrapper: {
    display: 'inline-flex'
  },
  navIcons: {
  },
  secondLevel: {
    position: 'absolute',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'start',
    left: 0,
    width: '100%',
    height: 34,
    borderTop: '1px solid #fff',
    top: 65,
    boxShadow: `0px 3px 5px -1px rgba(0, 0, 0, 0.2), 
                0px 6px 10px 0px rgba(0, 0, 0, 0.14), 
                0px 1px 18px 0px rgba(0, 0, 0, 0.12)`,
    zIndex: 1,
  },
  typography: {
    padding: theme.spacing.unit * 3,
  },
})

const Nav = (
  {classes, items, value, handleChange, handlePopoverClose, handlePopoverOpen, open
  }) => (
      <div className={classes.root}>
        <Tabs
          value={value}
          onChange={handleChange}
          classes={{ root: classes.tabsRoot, indicator: classes.tabsIndicator }}
        >
          <Tab disableRipple
            icon={<HomeIcon viewBox="6 156 600 600" className={classes.navIcons}/>}
            classes={{ root: classes.tabRoot, selected: classes.tabSelected }}
          />
          <Tab
            icon={<MonitoringIcon viewBox="0 0 600 600"/>}
            classes={{ root: classes.tabRoot, selected: classes.tabSelected }}
          />
          <Tab
            icon={<ReportingIcon viewBox="0 0 600 600"/>}
            classes={{ root: classes.tabRoot, selected: classes.tabSelected }}
          />
          <Tab
            icon={<ConfigurationIcon viewBox="0 0 600 600"/>}
            classes={{ root: classes.tabRoot, selected: classes.tabSelected }}
          />
          <Tab
            icon={<AdministrationIcon viewBox="6 156 600 600"/>}
            classes={{ root: classes.tabRoot, selected: classes.tabSelected }}
          />
        </Tabs>
        {
          items.map((item, index) => {
            if(value === index) {
              return (
                <div className={classes.wrapper} key={index}>
                  <div className={classes.secondLevel} key={index} style={{ backgroundColor: item.color }}>
                    {
                      item.children.map((secondItem, i) => {
                        return <SecondLevel
                        key={i}
                        id={i}
                        item={secondItem}
                        open={open}
                        />
                      })
                    }
                  </div>
                </div>
              )
            }
          })
        }
      </div>
)

export default withStyles(styles)(Nav)
