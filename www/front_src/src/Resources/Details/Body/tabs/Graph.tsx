import * as React from 'react';

import { Grid, Paper, Theme } from '@material-ui/core';

import { SelectField } from '@centreon/ui';

import { makeStyles } from '@material-ui/styles';
import {
  labelLast24h,
  labelLast7Days,
  labelLast31Days,
} from '../../../translatedLabels';
import Graph from '../../../Graph';

const useStyles = makeStyles((theme: Theme) => ({
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
    <Grid container direction="column" spacing={2}>
      <Grid item>
        <Paper className={classes.header}>
          <SelectField
            className={classes.periodSelect}
            options={graphOptions}
            selectedOptionId="last_24_h"
            onChange={() => {}}
          />
        </Paper>
      </Grid>
      <Grid item>
        <Paper className={classes.graphContainer}>
          <div className={classes.graph}>
            <Graph endpoint="http://localhost:5000/api/beta/graph" />
          </div>
        </Paper>
      </Grid>
    </Grid>
  );
};

export default GraphTab;
