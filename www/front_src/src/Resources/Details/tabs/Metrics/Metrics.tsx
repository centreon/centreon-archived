import * as React from 'react';

import { equals, last } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Paper, Typography } from '@material-ui/core';

import { StatusChip } from '@centreon/ui';

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
  calculationType: string;
}

const Metrics = ({
  infiniteScrollTriggerRef,
  metrics,
  calculationType,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <>
      <Paper>
        <Typography variant="subtitle2" color="textSecondary">
          {calculationType}
        </Typography>
      </Paper>
      {metrics.map((metric) => {
        const isLastMetric = equals(last(metrics), metric);

        const { id, name, resource, unit, value } = metric;

        return (
          <div key={id}>
            <Paper className={classes.serviceCard}>
              <div className={classes.serviceDetails}>
                <StatusChip
                  label={t(resource.status.name)}
                  severityCode={resource.status.severity_code}
                />
                <div className={classes.description}>
                  <Typography
                    variant="body1"
                    onClick={(): void => undefined}
                    style={{ cursor: 'pointer' }}
                  >
                    {resource.name}
                  </Typography>

                  <Typography variant="body2">{name}</Typography>
                </div>
                <Typography variant="body2">{`${value} (${unit})`}</Typography>
              </div>
            </Paper>
            {isLastMetric && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default Metrics;
