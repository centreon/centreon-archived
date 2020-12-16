import * as React from 'react';

import { path, isNil, equals, last } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Paper, Typography } from '@material-ui/core';

import { timeFormat } from '@centreon/ui';

import { labelNoDataFor } from '../../../translatedLabels';
import PerformanceGraph from '../../../Graph/Performance';
import { Resource } from '../../../models';

import { useStyles } from '.';

interface Props {
  services: Array<Resource>;
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  periodQueryParameters: string;
}

const ServiceGraphs = ({
  services,
  infiniteScrollTriggerRef,
  periodQueryParameters,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const [tooltipPosition, setTooltipPosition] = React.useState<number>();
  return (
    <>
      {services.map((service) => {
        const { id, name } = service;
        const isLastService = equals(last(services), service);

        const endpoint = path(
          ['links', 'endpoints', 'performance_graph'],
          service,
        );

        return (
          <div key={id}>
            <Paper className={classes.serviceCard}>
              {isNil(endpoint) ? (
                <Typography variant="body1" color="textPrimary">
                  {`${t(labelNoDataFor)} ${name}`}
                </Typography>
              ) : (
                <PerformanceGraph
                  endpoint={`${endpoint}${periodQueryParameters}`}
                  graphHeight={120}
                  xAxisTickFormat={timeFormat}
                  toggableLegend
                  timeline={[]}
                  onTooltipDisplay={setTooltipPosition}
                  tooltipPosition={tooltipPosition}
                />
              )}
            </Paper>
            {isLastService && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default ServiceGraphs;
