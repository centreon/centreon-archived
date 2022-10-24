import { useEffect } from 'react';

import { isNil } from 'ramda';
import { useAtom } from 'jotai';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { getUrlQueryParameters, setUrlQueryParameters } from '@centreon/ui';

import {
  customTimePeriodAtom,
  selectedTimePeriodAtom
} from '../Graph/Performance/TimePeriods/timePeriodAtoms';
import useTimePeriod from '../Graph/Performance/TimePeriods/useTimePeriod';

import { getTabIdFromLabel, getTabLabelFromId } from './tabs';
import { DetailsUrlQueryParameters } from './models';
import {
  defaultSelectedCustomTimePeriodAtom,
  defaultSelectedTimePeriodIdAtom,
  openDetailsTabIdAtom,
  selectedResourcesDetailsAtom,
  selectedResourceUuidAtom,
  sendingDetailsAtom,
  tabParametersAtom
} from './detailsAtoms';

const useDetails = (): void => {
  const [openDetailsTabId, setOpenDetailsTabId] = useAtom(openDetailsTabIdAtom);
  const [selectedResourceUuid, setSelectedResourceUuid] = useAtom(
    selectedResourceUuidAtom
  );
  const [selectedResource, setSelectedResource] = useAtom(
    selectedResourcesDetailsAtom
  );
  const [tabParameters, setTabParameters] = useAtom(tabParametersAtom);
  const customTimePeriod = useAtomValue(customTimePeriodAtom);
  const selectedTimePeriod = useAtomValue(selectedTimePeriodAtom);
  const sendingDetails = useAtomValue(sendingDetailsAtom);
  const setDefaultSelectedTimePeriodId = useUpdateAtom(
    defaultSelectedTimePeriodIdAtom
  );
  const setDefaultSelectedCustomTimePeriod = useUpdateAtom(
    defaultSelectedCustomTimePeriodAtom
  );

  useTimePeriod({
    sending: sendingDetails
  });

  useEffect(() => {
    const urlQueryParameters = getUrlQueryParameters();

    const detailsUrlQueryParameters =
      urlQueryParameters.details as DetailsUrlQueryParameters;

    if (isNil(detailsUrlQueryParameters)) {
      return;
    }

    const {
      uuid,
      id,
      parentId,
      parentType,
      tab,
      tabParameters: tabParametersFromUrl,
      selectedTimePeriodId,
      customTimePeriod: customTimePeriodFromUrl,
      resourcesDetailsEndpoint
    } = detailsUrlQueryParameters;

    if (!isNil(tab)) {
      setOpenDetailsTabId(getTabIdFromLabel(tab));
    }

    setSelectedResourceUuid(uuid);
    setSelectedResource({
      ...selectedResource,
      parentResourceId: parentId,
      parentResourceType: parentType,
      resourceId: id,
      resourcesDetailsEndpoint
    });
    setTabParameters(tabParametersFromUrl || {});
    setDefaultSelectedTimePeriodId(selectedTimePeriodId);
    setDefaultSelectedCustomTimePeriod(customTimePeriodFromUrl);
  }, []);

  useEffect(() => {
    setUrlQueryParameters([
      {
        name: 'details',
        value: {
          customTimePeriod,
          id: selectedResource?.resourceId,
          parentId: selectedResource?.parentResourceId,
          parentType: selectedResource?.parentResourceType,
          resourcesDetailsEndpoint: selectedResource?.resourcesDetailsEndpoint,
          selectedTimePeriodId: selectedTimePeriod?.id,
          tab: getTabLabelFromId(openDetailsTabId),
          tabParameters,
          uuid: selectedResourceUuid
        }
      }
    ]);
  }, [
    openDetailsTabId,
    selectedResource?.resourceId,
    selectedResource?.parentResourceType,
    selectedResource?.parentResourceId,
    selectedResource?.resourcesDetailsEndpoint,
    tabParameters,
    selectedTimePeriod,
    customTimePeriod,
    selectedResourceUuid
  ]);
};

export default useDetails;
