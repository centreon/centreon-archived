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
  selectedResourceIdAtom,
  selectedResourceParentIdAtom,
  selectedResourceParentTypeAtom,
  selectedResourceTypeAtom,
  selectedResourceUuidAtom,
  sendingDetailsAtom,
  tabParametersAtom,
} from './detailsAtoms';

const useDetails = (): void => {
  const [openDetailsTabId, setOpenDetailsTabId] = useAtom(openDetailsTabIdAtom);
  const [selectedResourceUuid, setSelectedResourceUuid] = useAtom(
    selectedResourceUuidAtom,
  );
  const [selectedResourceId, setSelectedResourceId] = useAtom(
    selectedResourceIdAtom,
  );
  const [selectedResourceParentId, setSelectedResourceParentId] = useAtom(
    selectedResourceParentIdAtom,
  );
  const [selectedResourceType, setSelectedResourceType] = useAtom(
    selectedResourceTypeAtom,
  );
  const [selectedResourceParentType, setSelectedResourceParentType] = useAtom(
    selectedResourceParentTypeAtom,
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
      parentId,
      type,
      parentType,
      tab,
      tabParameters: tabParametersFromUrl,
      selectedTimePeriodId,
      customTimePeriod: customTimePeriodFromUrl,
    } = detailsUrlQueryParameters;

    if (!isNil(tab)) {
      setOpenDetailsTabId(getTabIdFromLabel(tab));
    }

    setSelectedResourceUuid(uuid);
    setSelectedResourceId(id);
    setSelectedResourceParentId(parentId);
    setSelectedResourceType(type);
    setSelectedResourceParentType(parentType);
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
          id: selectedResourceId,
          parentId: selectedResourceParentId,
          parentType: selectedResourceParentType,
          selectedTimePeriodId: selectedTimePeriod?.id,
          tab: getTabLabelFromId(openDetailsTabId),
          tabParameters,
          type: selectedResourceType,
          uuid: selectedResourceUuid,
        },
      },
    ]);
  }, [
    openDetailsTabId,
    selectedResourceId,
    selectedResourceType,
    selectedResourceParentType,
    selectedResourceParentType,
    tabParameters,
    selectedTimePeriod,
    customTimePeriod,
  ]);
};

export default useDetails;
