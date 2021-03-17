import * as React from 'react';

import { equals, last } from 'ramda';

import { makeStyles, Paper } from '@material-ui/core';

import { MetaServiceMetric } from './models';

const useStyles = makeStyles((theme) => ({
  serviceCard: {
    padding: theme.spacing(1),
  },
  serviceDetails: {
    display: 'grid',
    gridAutoFlow: 'columns',
    gridTemplateColumns: 'auto 1fr auto',
    gridGap: theme.spacing(2),
    alignItems: 'center',
  },
  description: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
  },
}));

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  metrics: Array<MetaServiceMetric>;
}

const Metrics = ({ infiniteScrollTriggerRef, metrics }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      {metrics.map((metric) => {
        const isLastMetric = equals(last(metrics), metric);

        const { id, name, unit } = metric;

        return (
          <div key={id}>
            <Paper className={classes.serviceCard}>{`${name} (${unit})`}</Paper>
            {isLastMetric && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default Metrics;
