import * as React from 'react';

import { isNil, isEmpty, always, ifElse, equals } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Paper, Typography } from '@material-ui/core';
import GraphIcon from '@material-ui/icons/BarChart';
import ListIcon from '@material-ui/icons/List';

import { useRequest } from '@centreon/ui';

import { TabProps, detailsTabId } from '..';
import { useResourceContext } from '../../../Context';
import {
  labelNoResultsFound,
  labelSwitchToGraph,
  labelSwitchToList,
} from '../../../translatedLabels';
import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';

import ServiceGraphs from './Graphs';
import ServiceList from './List';
import Listing from './Listing';
import LoadingSkeleton from './LoadingSkeleton';

const useStyles = makeStyles((theme) => ({
  services: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
  serviceDetails: {
    display: 'grid',
    gridAutoFlow: 'columns',
    gridTemplateColumns: 'auto 1fr auto',
    gridGap: theme.spacing(2),
    alignItems: 'center',
  },
  serviceCard: {
    padding: theme.spacing(1),
  },
  noResultContainer: {
    padding: theme.spacing(1),
  },
}));

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
    setOpenDetailsTabId,
    selectedResourceId,
  } = useResourceContext();

  const [services, setServices] = React.useState<Array<Resource>>();
  const [graphMode, setGraphMode] = React.useState<boolean>(false);

  const { sendRequest } = useRequest({
    request: listResources,
  });

  React.useEffect(() => {
    if (isNil(details) || details.type === 'service') {
      return;
    }

    sendRequest({
      limit: 100,
      resourceTypes: ['service'],
      search: {
        conditions: [
          {
            field: 'h.name',
            values: {
              $eq: details.name,
            },
          },
        ],
      },
    }).then(({ result }) => setServices(result));
  }, [details]);

  React.useEffect(() => {
    if (selectedResourceId !== details?.id) {
      setServices(undefined);
    }
  }, [selectedResourceId]);

  const selectService = (serviceId): void => {
    setOpenDetailsTabId(detailsTabId);
    setSelectedResourceParentType('host');
    setSelectedResourceParentId(details?.id);
    setSelectedResourceId(serviceId);
    setSelectedResourceType('service');
  };

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

  return ifElse(
    equals(true),
    always(
      <Listing
        list={<ServiceGraphs services={services} />}
        switchButtonLabel={labelSwitchToList}
        switchButtonIcon={<ListIcon />}
        onSwitchButtonClick={(): void => setGraphMode(false)}
      />,
    ),
    always(
      <Listing
        list={
          <ServiceList services={services} onSelectService={selectService} />
        }
        switchButtonLabel={labelSwitchToGraph}
        switchButtonIcon={<GraphIcon />}
        onSwitchButtonClick={(): void => setGraphMode(true)}
      />,
    ),
  )(graphMode);
};

export default ServicesTab;
export { useStyles };
