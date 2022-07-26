import { useEffect } from 'react';

import { isNil } from 'ramda';
import { useAtom } from 'jotai';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { getUrlQueryParameters, setUrlQueryParameters } from '@centreon/ui';

import {
  customTimePeriodAtom,
  selectedTimePeriodAtom,
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
  tabParametersAtom,
} from './detailsAtoms';

const useDetails = (): void => {
  const [openDetailsTabId, setOpenDetailsTabId] = useAtom(openDetailsTabIdAtom);
  const [selectedResourceUuid, setSelectedResourceUuid] = useAtom(
    selectedResourceUuidAtom,
  );
  const [selectedResource, setSelectedResource] = useAtom(
    selectedResourcesDetailsAtom,
  );
  const [tabParameters, setTabParameters] = useAtom(tabParametersAtom);
  const customTimePeriod = useAtomValue(customTimePeriodAtom);
  const selectedTimePeriod = useAtomValue(selectedTimePeriodAtom);
  const sendingDetails = useAtomValue(sendingDetailsAtom);
  const setDefaultSelectedTimePeriodId = useUpdateAtom(
    defaultSelectedTimePeriodIdAtom,
  );
  const setDefaultSelectedCustomTimePeriod = useUpdateAtom(
    defaultSelectedCustomTimePeriodAtom,
  );

  useTimePeriod({
    sending: sendingDetails,
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
      type,
      tab,
      tabParameters: tabParametersFromUrl,
      selectedTimePeriodId,
      customTimePeriod: customTimePeriodFromUrl,
    } = detailsUrlQueryParameters;

    if (!isNil(tab)) {
      setOpenDetailsTabId(getTabIdFromLabel(tab));
    }

    setSelectedResourceUuid(uuid);
    setSelectedResource({ ...selectedResource, resourceId: id });
    setSelectedResource({ ...selectedResource, selectedResourceType: type });
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
          selectedTimePeriodId: selectedTimePeriod?.id,
          tab: getTabLabelFromId(openDetailsTabId),
          tabParameters,
          type: selectedResource?.selectedResourceType,
          uuid: selectedResourceUuid,
        },
      },
    ]);
  }, [
    openDetailsTabId,
    selectedResource?.resourceId,
    selectedResource?.selectedResourceType,
    tabParameters,
    selectedTimePeriod,
    customTimePeriod,
  ]);
};

export default useDetails;
