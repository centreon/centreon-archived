import * as React from 'react';

import { Tabs, Tab, makeStyles, AppBar } from '@material-ui/core';

import { DetailsSectionProps } from '..';
import { TabEndpoints } from './models';
import { TabById, tabs } from './tabs';

const useStyles = makeStyles((theme) => {
  return {
    body: {
      display: 'grid',
      gridTemplateRows: 'auto 1fr',
      height: '100%',
    },
    contentContainer: {
      backgroundColor: theme.palette.background.default,
      position: 'relative',
    },
    contentTab: {
      position: 'absolute',
      bottom: 0,
      left: 0,
      right: 0,
      top: 0,
      overflowY: 'auto',
      overflowX: 'hidden',
      padding: 10,
    },
  };
});

type Props = {
  endpoints: TabEndpoints;
  openTabId: number;
} & DetailsSectionProps;

const Body = ({ details, endpoints, openTabId }: Props): JSX.Element => {
  const classes = useStyles();

  const [selectedTabId, setSelectedTabId] = React.useState(0);

  React.useEffect(() => {
    setSelectedTabId(openTabId);
  }, [openTabId]);

  const changeSelectedTabId = (_, id): void => {
    setSelectedTabId(id);
  };

  return (
    <div className={classes.body}>
      <AppBar position="static" color="default">
        <Tabs
          variant="fullWidth"
          value={selectedTabId}
          indicatorColor="primary"
          textColor="primary"
          onChange={changeSelectedTabId}
        >
          {tabs
            .filter(({ visible }) => visible(endpoints))
            .map(({ id, title }) => (
              <Tab key={id} label={title} disabled={details === undefined} />
            ))}
        </Tabs>
      </AppBar>
      <div className={classes.contentContainer}>
        <div className={classes.contentTab}>
          <TabById id={selectedTabId} details={details} endpoints={endpoints} />
        </div>
      </div>
    </div>
  );
};

export default Body;
