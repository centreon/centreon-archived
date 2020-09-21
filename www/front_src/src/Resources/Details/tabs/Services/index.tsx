import * as React from 'react';

import { isNil } from 'ramda';
import { Skeleton } from '@material-ui/lab';
import { useTranslation } from 'react-i18next';

import { makeStyles, Paper, Typography } from '@material-ui/core';

import { useRequest, StatusChip } from '@centreon/ui';

import { TabProps } from '..';
import { listServices } from './api';
import { listServicesDecoder } from './api/decoders';
import { Service } from './models';
import { useResourceContext } from '../../../Context';

const useStyles = makeStyles((theme) => ({
  services: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
  service: {
    display: 'grid',
    gridAutoFlow: 'columns',
    gridTemplateColumns: 'auto 1fr auto',
    padding: theme.spacing(1),
    gridGap: theme.spacing(2),
    alignItems: 'center',
  },
  description: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
  },
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const serviceLoadingSkeleton = (
    <div className={classes.service}>
      <Skeleton variant="circle" width={25} height={25} />
      <Skeleton height={25} />
      <Skeleton width={50} height={25} />
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

  const {
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
  } = useResourceContext();

  const [services, setServices] = React.useState<Array<Service>>();

  const { sendRequest } = useRequest({
    request: listServices,
    decoder: listServicesDecoder,
  });

  React.useEffect(() => {
    if (isNil(details) || details.type === 'service') {
      return;
    }

    sendRequest(details.id).then(({ result }) => setServices(result));
  }, [details]);

  const selectService = (serviceId) => (): void => {
    setSelectedResourceParentType('host');
    setSelectedResourceParentId(details?.id);
    setSelectedResourceId(serviceId);
    setSelectedResourceType('service');
  };

  const getContent = (): JSX.Element => {
    if (isNil(details) || isNil(services)) {
      return <LoadingSkeleton />;
    }

    return (
      <>
        {services.map(({ id, status, name, output, duration }) => {
          return (
            <Paper key={id} className={classes.service}>
              <StatusChip
                label={t(status.name)}
                severityCode={status.severity_code}
              />
              <div className={classes.description}>
                <Typography
                  variant="body1"
                  onClick={selectService(id)}
                  style={{ cursor: 'pointer' }}
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
