import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, path, pathOr } from 'ramda';

import GraphIcon from '@material-ui/icons/BarChart';
import ListIcon from '@material-ui/icons/List';

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
    defaultSelectedTimePeriodId: path(
      ['services', 'graphTimePeriod', 'selectedTimePeriodId'],
      tabParameters,
    ),
    defaultSelectedCustomTimePeriod: path(
      ['services', 'graphTimePeriod', 'selectedCustomTimePeriod'],
      tabParameters,
    ),
    defaultGraphOptions: path(
      ['services', 'graphTimePeriod', 'graphOptions'],
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
      page: atPage,
      resourceTypes: ['service'],
      onlyWithPerformanceData: graphMode ? true : undefined,
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
      graphTimePeriod: pathOr(
        {},
        ['services', 'graphTimePeriod'],
        tabParameters,
      ),
      graphMode: mode,
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
    graphTabParameters: tabParameters.services?.graphTimePeriod,
    changeTabGraphOptions,
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
        title={t(labelSwitch)}
        ariaLabel={t(labelSwitch)}
        disabled={loading}
        onClick={switchMode}
      >
        {switchIcon}
      </IconButton>
      <GraphOptionsContext.Provider value={graphOptions}>
        <InfiniteScroll<Resource>
          preventReloadWhen={details?.type !== 'host'}
          sendListingRequest={sendListingRequest}
          details={details}
          loadingSkeleton={<LoadingSkeleton />}
          filter={
            graphMode ? (
              <TimePeriodButtonGroup
                selectedTimePeriodId={selectedTimePeriod?.id}
                onChange={changeSelectedTimePeriod}
                disabled={loading}
                customTimePeriod={customTimePeriod}
                changeCustomTimePeriod={changeCustomTimePeriod}
              />
            ) : undefined
          }
          reloadDependencies={[graphMode]}
          loading={sending}
          limit={limit}
        >
          {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
            const displayGraphs = graphMode && canDisplayGraphs;

            return displayGraphs ? (
              <ServiceGraphs
                services={entities}
                infiniteScrollTriggerRef={infiniteScrollTriggerRef}
                periodQueryParameters={periodQueryParameters}
                getIntervalDates={getIntervalDates}
                selectedTimePeriod={selectedTimePeriod}
                customTimePeriod={customTimePeriod}
                adjustTimePeriod={adjustTimePeriod}
                resourceDetailsUpdated={resourceDetailsUpdated}
              />
            ) : (
              <ServiceList
                services={entities}
                onSelectService={selectResource}
                infiniteScrollTriggerRef={infiniteScrollTriggerRef}
              />
            );
          }}
        </InfiniteScroll>
      </GraphOptionsContext.Provider>
    </>
  );
};

const MemoizedServiceTabContent = memoizeComponent<ServicesTabContentProps>({
  memoProps: ['details', 'tabParameters'],
  Component: ServicesTabContent,
});

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const {
    selectResource,
    tabParameters,
    setServicesTabParameters,
  } = useResourceContext();

  return (
    <MemoizedServiceTabContent
      details={details}
      tabParameters={tabParameters}
      selectResource={selectResource}
      setServicesTabParameters={setServicesTabParameters}
    />
  );
};

export default ServicesTab;
