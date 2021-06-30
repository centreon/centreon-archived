import * as React from 'react';

import { isNil, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Skeleton } from '@material-ui/lab';
import { makeStyles, Paper, Typography } from '@material-ui/core';

import { useRequest, StatusChip } from '@centreon/ui';

import { useResourceContext } from '../../../Context';
import { labelNoResultsFound } from '../../../translatedLabels';
import { TabProps, detailsTabId } from '..';

import { listServices } from './api';
import { listServicesDecoder } from './api/decoders';
import { Service } from './models';

const useStyles = makeStyles((theme) => ({
  description: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
  },
  noResultContainer: {
    padding: theme.spacing(1),
  },
  service: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'columns',
    gridGap: theme.spacing(2),
    gridTemplateColumns: 'auto 1fr auto',
    padding: theme.spacing(1),
  },
  services: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  const serviceLoadingSkeleton = (
    <div className={classes.service}>
      <Skeleton height={25} variant="circle" width={25} />
      <Skeleton height={25} />
      <Skeleton height={25} width={50} />
    </div>
  );

  return (
    <div className={classes.services}>
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
    </div>
  );
};

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    setSelectedResourceUuid,
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
    setOpenDetailsTabId,
    selectedResourceId,
    selectedResourceUuid,
  } = useResourceContext();

  const [services, setServices] = React.useState<Array<Service>>();

  const { sendRequest } = useRequest({
    decoder: listServicesDecoder,
    request: listServices,
  });

  React.useEffect(() => {
    if (isNil(details) || details.type === 'service') {
      return;
    }

    sendRequest(details.id).then(({ result }) => setServices(result));
  }, [details]);

  React.useEffect(() => {
    if (selectedResourceId !== details?.id) {
      setServices(undefined);
    }
  }, [selectedResourceId]);

  const selectService = (service) => (): void => {
    setOpenDetailsTabId(detailsTabId);
    setSelectedResourceUuid(`${selectedResourceUuid}-s${service.id}`);
    setSelectedResourceId(service.id);
    setSelectedResourceType('service');
    setSelectedResourceParentType('host');
    setSelectedResourceParentId(selectedResourceId);
  };

  const getContent = (): JSX.Element => {
    if (isNil(details) || isNil(services)) {
      return <LoadingSkeleton />;
    }

    if (isEmpty(services)) {
      return (
        <Paper className={classes.noResultContainer}>
          <Typography align="center" variant="body1">
            {t(labelNoResultsFound)}
          </Typography>
        </Paper>
      );
    }

    return (
      <>
        {services.map((service) => {
          const { id, name, status, output, duration } = service;

          return (
            <Paper className={classes.service} key={id}>
              <StatusChip
                label={t(status.name)}
                severityCode={status.severity_code}
              />
              <div className={classes.description}>
                <Typography
                  style={{ cursor: 'pointer' }}
                  variant="body1"
                  onClick={selectService(service)}
                >
                  {name}
                </Typography>
                <Typography variant="body2">{output}</Typography>
              </div>
              {duration && <Typography variant="body2">{duration}</Typography>}
            </Paper>
          );
        })}
      </>
    );
  };

  return <div className={classes.services}>{getContent()}</div>;
};

export default ServicesTab;
