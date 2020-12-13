import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Paper, Typography, makeStyles } from '@material-ui/core';

import { StatusChip } from '@centreon/ui';

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

const ServiceList = ({ services, onSelectService }): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return services.map(({ id, name, status, information, duration }) => {
    return (
      <Paper key={id} className={classes.serviceCard}>
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
    );
  });
};

export default ServiceList;
