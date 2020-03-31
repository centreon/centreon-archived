import * as React from 'react';

import { Paper, Theme } from '@material-ui/core';

import { SelectField } from '@centreon/ui';

import { makeStyles } from '@material-ui/styles';
import {
  labelLast24h,
  labelLast7Days,
  labelLast31Days,
} from '../../../translatedLabels';
import Graph from '../../../Graph';
import StatusGraph from '../../../Graph/Status';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    gridRowGap: theme.spacing(2),
  },
  header: {
    padding: theme.spacing(2),
  },
  periodSelect: {
    width: 250,
  },
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(4),
    gridTemplateRows: '250px 100px',
  },
  graph: {
    margin: 'auto',
    height: '100%',
  },
  performance: {
    width: '100%',
  },
  status: {
    marginTop: theme.spacing(2),
    width: '90%',
  },
}));

const graphOptions = [
  { name: labelLast24h, id: 'last_24_h' },
  { name: labelLast7Days, id: 'last_7_days' },
  { name: labelLast31Days, id: 'last_31_days' },
];

const GraphTab = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.container}>
      <Paper className={classes.header}>
        <SelectField
          className={classes.periodSelect}
          options={graphOptions}
          selectedOptionId="last_24_h"
          onChange={() => {}}
        />
      </Paper>
      <Paper className={classes.graphContainer}>
        <div className={`${classes.graph} ${classes.performance}`}>
          <Graph endpoint="http://localhost:5000/api/beta/graph" />
        </div>
        <div className={`${classes.graph} ${classes.status}`}>
          <StatusGraph endpoint="http://localhost:5000/api/beta/status" />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
