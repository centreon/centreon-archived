import { useEffect } from 'react';

import { isNil, ifElse, pathEq, always, pathOr } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';

import { useRequest, getData } from '@centreon/ui';

import {
  labelNoResourceFound,
  labelSomethingWentWrong,
} from '../translatedLabels';
import useTimePeriod from '../Graph/Performance/TimePeriods/useTimePeriod';
import {
  customTimePeriodAtom,
  getNewCustomTimePeriod,
  resourceDetailsUpdatedAtom,
  selectedTimePeriodAtom,
} from '../Graph/Performance/TimePeriods/timePeriodAtoms';
import { ResourceDetails } from '../Details/models';
import {
  clearSelectedResourceDerivedAtom,
  detailsAtom,
  selectedResourceDetailsEndpointDerivedAtom,
  selectedResourceIdAtom,
  selectedResourceUuidAtom,
  sendingDetailsAtom,
} from '../Details/detailsAtoms';
import { ChangeCustomTimePeriodProps } from '../Details/tabs/Graph/models';

export interface DetailsState {
  changeCustomTimePeriod: (props: ChangeCustomTimePeriodProps) => void;
  loadDetails: () => void;
}

const useLoadDetails = (): DetailsState => {
  const { t } = useTranslation();

  const { sendRequest } = useRequest<ResourceDetails>({
    getErrorMessage: ifElse(
      pathEq(['response', 'status'], 404),
      always(t(labelNoResourceFound)),
      pathOr(t(labelSomethingWentWrong), ['response', 'data', 'message']),
    ),
    request: getData,
  });

  const [customTimePeriod, setCustomTimePeriod] = useAtom(customTimePeriodAtom);
  const selectedResourceId = useAtomValue(selectedResourceIdAtom);
  const selectedResourceUuid = useAtomValue(selectedResourceUuidAtom);
  const selectedResourceDetailsEndpoint = useAtomValue(
    selectedResourceDetailsEndpointDerivedAtom,
  );
  const sendingDetails = useAtomValue(sendingDetailsAtom);
  const setDetails = useUpdateAtom(detailsAtom);
  const clearSelectedResource = useUpdateAtom(clearSelectedResourceDerivedAtom);
  const setSelectedTimePeriod = useUpdateAtom(selectedTimePeriodAtom);
  const setResourceDetailsUpdated = useUpdateAtom(resourceDetailsUpdatedAtom);

  useTimePeriod({
    sending: sendingDetails,
  });

  const loadDetails = (): void => {
    if (isNil(selectedResourceId)) {
      return;
    }

    sendRequest({
      endpoint: selectedResourceDetailsEndpoint,
    })
      .then(setDetails)
      .catch(() => {
        clearSelectedResource();
      });
  };

  const changeCustomTimePeriod = ({ date, property }): void => {
    const newCustomTimePeriod = getNewCustomTimePeriod({
      ...customTimePeriod,
      [property]: date,
    });

    setCustomTimePeriod(newCustomTimePeriod);
    setSelectedTimePeriod(null);
    setResourceDetailsUpdated(false);
  };

  useEffect(() => {
    setDetails(undefined);
    loadDetails();
  }, [selectedResourceUuid]);

  return {
    changeCustomTimePeriod,
    loadDetails,
  };
};

export default useLoadDetails;
