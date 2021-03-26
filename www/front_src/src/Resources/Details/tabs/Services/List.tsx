import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { equals, last } from 'ramda';

import { Paper, Typography, makeStyles } from '@material-ui/core';

import { Resource } from '../../../models';
import CompactStatusChip from '../CompactStatusChip';
import OutputInformation from '../OutputInformation';

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
  onSelectService: (service: Resource) => void;
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
}

const ServiceList = ({
  services,
  onSelectService,
  infiniteScrollTriggerRef,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <>
      {services.map((service) => {
        const isLastService = equals(last(services), service);
        const { id, name, status, information, duration } = service;

        return (
          <div key={id}>
            <Paper className={classes.serviceCard}>
              <div className={classes.serviceDetails}>
                <CompactStatusChip
                  label={t(status.name)}
                  severityCode={status.severity_code}
                />
                <div className={classes.description}>
                  <Typography
                    variant="body1"
                    onClick={(): void => onSelectService(service)}
                    style={{ cursor: 'pointer' }}
                  >
                    {name}
                  </Typography>
                  <OutputInformation content={information} />
                </div>
                {duration && (
                  <Typography variant="caption">{duration}</Typography>
                )}
              </div>
            </Paper>
            {isLastService && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </>
  );
};

export default ServiceList;
