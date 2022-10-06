import { RefObject } from 'react';

import { useUpdateAtom } from 'jotai/utils';
import { equals, last } from 'ramda';

import ShowChartOutlinedIcon from '@mui/icons-material/ShowChartOutlined';
import { Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import ShortTypeChip from '../../../ShortTypeChip';
import {
  selectedResourcesDetailsAtom,
  selectResourceDerivedAtom,
} from '../../detailsAtoms';
import Card from '../Details/Card';
import SelectableResourceName from '../Details/SelectableResourceName';

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

interface Props {
  infiniteScrollTriggerRef: RefObject<HTMLDivElement>;
  metrics: Array<MetaServiceMetric>;
}

const Metrics = ({ infiniteScrollTriggerRef, metrics }: Props): JSX.Element => {
  const classes = useStyles();

  const selectResource = useUpdateAtom(selectResourceDerivedAtom);
  const setSelectedResourceDetails = useUpdateAtom(
    selectedResourcesDetailsAtom,
  );

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
                    onSelect={(): void => selectResource(resource)}
                  />
                </div>
                <div className={classes.iconValuePair}>
                  <ShortTypeChip label={resource.short_type as string} />
                  <SelectableResourceName
                    name={resource.name}
                    variant="body2"
                    onSelect={(): void =>
                      setSelectedResourceDetails({
                        resourceId: resource.id,
                        resourcesDetailsEndpoint:
                          resource.links?.endpoints?.details,
                      })
                    }
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
