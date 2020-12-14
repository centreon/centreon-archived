import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { equals, last } from 'ramda';

import { Paper, Typography, makeStyles } from '@material-ui/core';

import { StatusChip } from '@centreon/ui';

import { Resource } from '../../../models';

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
  services: Array<Resource>;
  onSelectService: () => void;
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
}

const ServiceList = ({
  services,
  onSelectService,
  infiniteScrollTriggerRef,
}): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return services.map((service) => {
    const isLastService = equals(last(services), service);
    const { id, name, status, information, duration } = service;

    return (
      <div key={id}>
        <Paper className={classes.serviceCard}>
          <div className={classes.serviceDetails}>
            <StatusChip
              label={t(status.name)}
              severityCode={status.severity_code}
            />
            <div className={classes.description}>
              <Typography
                variant="body1"
                onClick={(): void => onSelectService(id)}
                style={{ cursor: 'pointer' }}
              >
                {name}
              </Typography>
              <Typography variant="body2">{information}</Typography>
            </div>
            {duration && <Typography variant="body2">{duration}</Typography>}
          </div>
        </Paper>
        {isLastService && <div ref={infiniteScrollTriggerRef} />}
      </div>
    );
  });
};

export default ServiceList;
