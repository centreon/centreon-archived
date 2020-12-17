import * as React from 'react';

import { Theme, makeStyles } from '@material-ui/core';

import { TabProps } from '..';
import useTimePeriod from '../../../Graph/Performance/TimePeriodSelect/useTimePeriod';
import TimePeriodSelect from '../../../Graph/Performance/TimePeriodSelect';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    gridRowGap: theme.spacing(2),
  },
  exportToPngButton: {
    display: 'flex',
    justifyContent: 'space-between',
    margin: theme.spacing(0, 1, 1, 2),
  },
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(2, 1, 1),
    gridTemplateRows: '1fr',
  },
  graph: {
    margin: 'auto',
    height: '100%',
    width: '100%',
  },
}));

const GraphTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
    getIntervalDates,
  } = useTimePeriod();

  return (
    <div className={classes.container}>
      <TimePeriodSelect
        selectedTimePeriodId={selectedTimePeriod.id}
        onChange={changeSelectedTimePeriod}
      />
      <ExportablePerformanceGraphWithTimeline
        resource={details}
        graphHeight={280}
        periodQueryParameters={periodQueryParameters}
        getIntervalDates={getIntervalDates}
        selectedTimePeriod={selectedTimePeriod}
      />
    </div>
  );
};

export default GraphTab;
