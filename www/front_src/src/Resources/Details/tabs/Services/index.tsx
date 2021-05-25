import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, path, pathOr } from 'ramda';

import GraphIcon from '@material-ui/icons/esm/BarChart';
import ListIcon from '@material-ui/icons/esm/List';

import { useRequest, IconButton, ListingModel } from '@centreon/ui';

import { TabProps } from '..';
import { ResourceContext, useResourceContext } from '../../../Context';
import {
  labelSwitchToGraph,
  labelSwitchToList,
} from '../../../translatedLabels';
import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import memoizeComponent from '../../../memoizedComponent';
import useTimePeriod from '../../../Graph/Performance/TimePeriods/useTimePeriod';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import { GraphOptions } from '../../models';
import useGraphOptions, {
  GraphOptionsContext,
} from '../../../Graph/Performance/ExportableGraphWithTimeline/useGraphOptions';

import ServiceGraphs from './Graphs';
import ServiceList from './List';
import LoadingSkeleton from './LoadingSkeleton';

type ServicesTabContentProps = TabProps &
  Pick<
    ResourceContext,
    'selectResource' | 'tabParameters' | 'setServicesTabParameters'
  >;

const ServicesTabContent = ({
  details,
  tabParameters,
  selectResource,
  setServicesTabParameters,
}: ServicesTabContentProps): JSX.Element => {
  const { t } = useTranslation();

  const [graphMode, setGraphMode] = React.useState<boolean>(
    tabParameters.services?.graphMode || false,
  );

  const [canDisplayGraphs, setCanDisplayGraphs] = React.useState(false);

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
    getIntervalDates,
    customTimePeriod,
    changeCustomTimePeriod,
    adjustTimePeriod,
    resourceDetailsUpdated,
  } = useTimePeriod({
    defaultGraphOptions: path(
      ['services', 'graphTimePeriod', 'graphOptions'],
      tabParameters,
    ),
    defaultSelectedCustomTimePeriod: path(
      ['services', 'graphTimePeriod', 'selectedCustomTimePeriod'],
      tabParameters,
    ),
    defaultSelectedTimePeriodId: path(
      ['services', 'graphTimePeriod', 'selectedTimePeriodId'],
      tabParameters,
    ),
    details,
    onTimePeriodChange: (graphTimePeriod) => {
      setServicesTabParameters({
        graphMode,
        graphTimePeriod,
      });
    },
  });

  const { sendRequest, sending } = useRequest({
    request: listResources,
  });

  const limit = graphMode ? 6 : 30;

  const sendListingRequest = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<ListingModel<Resource>> => {
    return sendRequest({
      limit,
      onlyWithPerformanceData: graphMode ? true : undefined,
      page: atPage,
      resourceTypes: ['service'],
      search: {
        conditions: [
          {
            field: 'h.name',
            values: {
              $eq: details?.name,
            },
          },
        ],
      },
    });
  };

  const switchMode = (): void => {
    setCanDisplayGraphs(false);
    const mode = !graphMode;

    setGraphMode(mode);

    setServicesTabParameters({
      graphMode: mode,
      graphTimePeriod: pathOr(
        {},
        ['services', 'graphTimePeriod'],
        tabParameters,
      ),
    });
  };

  const changeTabGraphOptions = (graphOptions: GraphOptions) => {
    setServicesTabParameters({
      graphMode: tabParameters.services?.graphMode || false,
      graphTimePeriod: {
        ...tabParameters.services?.graphTimePeriod,
        graphOptions,
      },
    });
  };

  const graphOptions = useGraphOptions({
    changeTabGraphOptions,
    graphTabParameters: tabParameters.services?.graphTimePeriod,
  });

  React.useEffect(() => {
    // To make sure that graphs are not displayed until 'entities' are reset
    setCanDisplayGraphs(true);
  }, [graphMode]);

  const labelSwitch = graphMode ? labelSwitchToList : labelSwitchToGraph;
  const switchIcon = graphMode ? <ListIcon /> : <GraphIcon />;

  const loading = isNil(details) || sending;

  return (
    <>
      <IconButton
        ariaLabel={t(labelSwitch)}
        disabled={loading}
        title={t(labelSwitch)}
        onClick={switchMode}
      >
        {switchIcon}
      </IconButton>
      <GraphOptionsContext.Provider value={graphOptions}>
        <InfiniteScroll<Resource>
          details={details}
          filter={
            graphMode ? (
              <TimePeriodButtonGroup
                changeCustomTimePeriod={changeCustomTimePeriod}
                customTimePeriod={customTimePeriod}
                disabled={loading}
                selectedTimePeriodId={selectedTimePeriod?.id}
                onChange={changeSelectedTimePeriod}
              />
            ) : undefined
          }
          limit={limit}
          loading={sending}
          loadingSkeleton={<LoadingSkeleton />}
          preventReloadWhen={details?.type !== 'host'}
          reloadDependencies={[graphMode]}
          sendListingRequest={sendListingRequest}
        >
          {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
            const displayGraphs = graphMode && canDisplayGraphs;

            return displayGraphs ? (
              <ServiceGraphs
                adjustTimePeriod={adjustTimePeriod}
                customTimePeriod={customTimePeriod}
                getIntervalDates={getIntervalDates}
                infiniteScrollTriggerRef={infiniteScrollTriggerRef}
                periodQueryParameters={periodQueryParameters}
                resourceDetailsUpdated={resourceDetailsUpdated}
                selectedTimePeriod={selectedTimePeriod}
                services={entities}
              />
            ) : (
              <ServiceList
                infiniteScrollTriggerRef={infiniteScrollTriggerRef}
                services={entities}
                onSelectService={selectResource}
              />
            );
          }}
        </InfiniteScroll>
      </GraphOptionsContext.Provider>
    </>
  );
};

const MemoizedServiceTabContent = memoizeComponent<ServicesTabContentProps>({
  Component: ServicesTabContent,
  memoProps: ['details', 'tabParameters'],
});

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const { selectResource, tabParameters, setServicesTabParameters } =
    useResourceContext();

  return (
    <MemoizedServiceTabContent
      details={details}
      selectResource={selectResource}
      setServicesTabParameters={setServicesTabParameters}
      tabParameters={tabParameters}
    />
  );
};

export default ServicesTab;
