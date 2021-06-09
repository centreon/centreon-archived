/* eslint-disable @typescript-eslint/naming-convention */
import * as React from 'react';

import { equals, last } from 'ramda';

import { makeStyles, Typography } from '@material-ui/core';
import ShowChartOutlinedIcon from '@material-ui/icons/ShowChartOutlined';

import { ResourceContext } from '../../../Context';
import Card from '../Details/Card';
import SelectableResourceName from '../Details/SelectableResourceName';
import { Resource } from '../../../models';
import ShortTypeChip from '../../../ShortTypeChip';

import { MetaServiceMetric } from './models';

const useStyles = makeStyles((theme) => ({
  card: {
    alignItems: 'center',
    display: 'grid',
    gridColumnGap: theme.spacing(2),
    gridTemplateColumns: '1fr 1fr auto',
    justifyItems: 'flex-start',
    width: '100%',
  },
  container: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
  iconValuePair: {
    alignItems: 'center',
    display: 'flex',
    flexDirection: 'row',
    gridGap: theme.spacing(1),
  },
  resources: {
    display: 'flex',
    flexDirection: 'column',
    gridGap: theme.spacing(1),
    overflow: 'hidden',
  },
}));

type Props = {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  metrics: Array<MetaServiceMetric>;
} & Pick<ResourceContext, 'selectResource'>;

const Metrics = ({
  infiniteScrollTriggerRef,
  metrics,
  selectResource,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      {metrics.map((metric) => {
        const isLastMetric = equals(last(metrics), metric);

        const { id, name, resource, unit, value } = metric;

        return (
          <Card key={id}>
            <div className={classes.card}>
              <div className={classes.resources}>
                <div className={classes.iconValuePair}>
                  <ShortTypeChip
                    label={resource.parent?.short_type as string}
                  />
                  <SelectableResourceName
                    name={resource.parent?.name as string}
                    variant="body2"
                    onSelect={() => selectResource(resource.parent as Resource)}
                  />
                </div>
                <div className={classes.iconValuePair}>
                  <ShortTypeChip label={resource.short_type as string} />
                  <SelectableResourceName
                    name={resource.name}
                    variant="body2"
                    onSelect={() => selectResource(resource)}
                  />
                </div>
              </div>
              <Typography align="left" variant="subtitle1">
                {name}
              </Typography>
              <div className={classes.iconValuePair}>
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
