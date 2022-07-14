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
  selectedResourceUuidAtom,
  sendingDetailsAtom,
  tabParametersAtom,
  selectedResourceDetailsEndpointAtom,
} from './detailsAtoms';

const useDetails = (): void => {
  const [openDetailsTabId, setOpenDetailsTabId] = useAtom(openDetailsTabIdAtom);
  const [selectedResourceUuid, setSelectedResourceUuid] = useAtom(
    selectedResourceUuidAtom,
  );
  const [tabParameters, setTabParameters] = useAtom(tabParametersAtom);
  const [selectedResourceDetailsEndpoint, setSelectedResourceDetailsEndpoint] =
    useAtom(selectedResourceDetailsEndpointAtom);
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
      tab,
      tabParameters: tabParametersFromUrl,
      selectedTimePeriodId,
      customTimePeriod: customTimePeriodFromUrl,
      selectedResourceDetailsEndpoint: selectedResourceDetailsEndpointFromUrl,
    } = detailsUrlQueryParameters;

    if (!isNil(tab)) {
      setOpenDetailsTabId(getTabIdFromLabel(tab));
    }

    setSelectedResourceUuid(uuid);
    setTabParameters(tabParametersFromUrl || {});
    setDefaultSelectedTimePeriodId(selectedTimePeriodId);
    setDefaultSelectedCustomTimePeriod(customTimePeriodFromUrl);
    setSelectedResourceDetailsEndpoint(selectedResourceDetailsEndpointFromUrl);
  }, []);

  useEffect(() => {
    setUrlQueryParameters([
      {
        name: 'details',
        value: {
          customTimePeriod,
          selectedResourceDetailsEndpoint,
          selectedTimePeriodId: selectedTimePeriod?.id,
          tab: getTabLabelFromId(openDetailsTabId),
          tabParameters,
          uuid: selectedResourceUuid,
        },
      },
    ]);
  }, [
    openDetailsTabId,
    tabParameters,
    selectedTimePeriod,
    customTimePeriod,
    selectedResourceDetailsEndpoint,
  ]);
};

export default useDetails;
