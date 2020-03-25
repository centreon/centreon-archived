import * as React from 'react';

import { Tabs, Tab, makeStyles } from '@material-ui/core';

import { labelDetails, labelGraph } from '../../translatedLabels';
import { DetailsSectionProps } from '..';
import DetailsTab from './DetailsTab';

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
      padding: 10,
    },
  };
});

const Body = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles(details);

  const [selectedTabId, setSelectedTabId] = React.useState(0);

  const changeSelectedTabId = (_, id): void => {
    setSelectedTabId(id);
  };

  return (
    <div className={classes.body}>
      <Tabs
        variant="fullWidth"
        value={selectedTabId}
        indicatorColor="primary"
        textColor="primary"
        onChange={changeSelectedTabId}
      >
        <Tab label={labelDetails} />
        <Tab label={labelGraph} />
      </Tabs>
      <div className={classes.contentContainer}>
        <div className={classes.contentTab}>
          {selectedTabId === 0 && <DetailsTab details={details} />}
        </div>
      </div>
    </div>
  );
};

export default Body;
