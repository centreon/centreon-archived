import * as React from 'react';

import { Tabs, Tab, makeStyles } from '@material-ui/core';

import { labelDetails, labelGraph } from '../../translatedLabels';
import { DetailsSectionProps } from '..';
import DetailsTab from './DetailsTab';

const useStyles = makeStyles((theme) => {
  return {
    content: {
      padding: 10,
      backgroundColor: theme.palette.background.default,
      height: '100%',
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
    <>
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
      <div className={classes.content}>
        {selectedTabId === 0 && <DetailsTab details={details} />}
      </div>
    </>
  );
};

export default Body;
