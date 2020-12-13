import * as React from 'react';

import { path, isNil } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Paper, Typography } from '@material-ui/core';

import { timeFormat } from '@centreon/ui';

import { labelNoDataFor } from '../../../translatedLabels';
import PerformanceGraph from '../../../Graph/Performance';
import TimePeriodSelect from '../../../Graph/Performance/TimePeriodSelect';
import { Resource } from '../../../models';
import useTimePeriod from '../../../Graph/Performance/TimePeriodSelect/useTimePeriod';

import { useStyles } from '.';

interface Props {
  services: Array<Resource>;
}

const ServiceGraphs = ({ services }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
  } = useTimePeriod();

  return (
    <>
      <TimePeriodSelect
        selectedTimePeriodId={selectedTimePeriod.id}
        onChange={changeSelectedTimePeriod}
      />
      {services.map((service) => {
        const { id, name } = service;

        const graphEndpoint = path(
          ['links', 'endpoints', 'performance_graph'],
          service,
        );

        return (
          <Paper key={id} className={classes.serviceCard}>
            {isNil(graphEndpoint) ? (
              <Typography variant="body1" color="textPrimary">
                {`${t(labelNoDataFor)} ${name}`}
              </Typography>
            ) : (
              <PerformanceGraph
                endpoint={`${graphEndpoint}${periodQueryParameters}`}
                graphHeight={120}
                xAxisTickFormat={timeFormat}
                toggableLegend
                timeline={[]}
              />
            )}
          </Paper>
        );
      })}
    </>
  );
};

export default ServiceGraphs;
