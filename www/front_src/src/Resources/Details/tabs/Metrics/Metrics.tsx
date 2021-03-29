/* eslint-disable @typescript-eslint/naming-convention */
import * as React from 'react';

import { equals, last } from 'ramda';

import { makeStyles, Typography } from '@material-ui/core';
import ShowChartOutlinedIcon from '@material-ui/icons/ShowChartOutlined';

import { SeverityCode, StatusChip } from '@centreon/ui';

import { useResourceContext } from '../../../Context';
import Card from '../Details/Card';
import SelectableResourceName from '../Details/SelectableResourceName';

import { MetaServiceMetric } from './models';

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
  card: {
    display: 'grid',
    gridColumnGap: theme.spacing(2.5),
    alignItems: 'center',
    gridTemplateColumns: '1fr auto auto',
    width: '100%',
  },
  service: {
    display: 'flex',
    gridGap: theme.spacing(1),
    flexDirection: 'row',
    alignItems: 'center',
  },
}));

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  metrics: Array<MetaServiceMetric>;
}

const Metrics = ({ infiniteScrollTriggerRef, metrics }: Props): JSX.Element => {
  const classes = useStyles();

  const { selectResource } = useResourceContext();

  return (
    <>
      {metrics.map((metric) => {
        const isLastMetric = equals(last(metrics), metric);

        const { id, name, resource, unit, value } = metric;

        return (
          <Card key={id}>
            <div className={classes.card}>
              <Typography variant="subtitle1">{name}</Typography>
              <div className={classes.service}>
                <StatusChip
                  label={resource.short_type}
                  severityCode={SeverityCode.None}
                />
                <SelectableResourceName
                  name={resource.name}
                  onSelect={() => selectResource(resource)}
                />
              </div>
              <div className={classes.service}>
                <ShowChartOutlinedIcon color="primary" />
                <Typography>{`${value} (${unit})`}</Typography>
              </div>
            </div>
            {isLastMetric && <div ref={infiniteScrollTriggerRef} />}
          </Card>
        );
      })}
    </>
  );
};

export default Metrics;
