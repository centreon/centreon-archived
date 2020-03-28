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

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 250px',
    gridRowGap: theme.spacing(2),
  },
  header: {
    padding: theme.spacing(2),
  },
  periodSelect: {
    width: 250,
  },
  graphContainer: {
    display: 'flex',
    padding: theme.spacing(4),
  },
  graph: {
    margin: 'auto',
    height: '100%',
    width: '100%',
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
        <div className={classes.graph}>
          <Graph endpoint="http://localhost:5000/api/beta/graph" />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
