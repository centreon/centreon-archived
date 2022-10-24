import { useEffect } from 'react';

import { equals, isNil, not, propOr } from 'ramda';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';
import { useAtom } from 'jotai';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import {
  getTimePeriodById,
  TimePeriodId
} from '../../../Details/tabs/Graph/models';
import {
  defaultSelectedCustomTimePeriodAtom,
  defaultSelectedTimePeriodIdAtom,
  detailsAtom
} from '../../../Details/detailsAtoms';

import {
  customTimePeriodAtom,
  getNewCustomTimePeriod,
  getTimeperiodFromNow,
  resourceDetailsUpdatedAtom,
  selectedTimePeriodAtom
} from './timePeriodAtoms';

dayjs.extend(duration);

interface Props {
  sending?: boolean;
}

const useTimePeriod = ({ sending = false }: Props): void => {
  const [customTimePeriod, setCustomTimePeriod] = useAtom(customTimePeriodAtom);
  const [selectedTimePeriod, setSelectedTimePeriod] = useAtom(
    selectedTimePeriodAtom
  );
  const details = useAtomValue(detailsAtom);
  const defaultSelectedTimePeriodId = useAtomValue(
    defaultSelectedTimePeriodIdAtom
  );
  const defaultSelectedCustomTimePeriod = useAtomValue(
    defaultSelectedCustomTimePeriodAtom
  );
  const setResourceDetailsUpdated = useUpdateAtom(resourceDetailsUpdatedAtom);

  useEffect(() => {
    if (isNil(selectedTimePeriod) || isNil(details) || not(sending)) {
      return;
    }

    const newTimePeriod = getTimeperiodFromNow(selectedTimePeriod);

    setCustomTimePeriod(newTimePeriod);
    setResourceDetailsUpdated(true);
  }, [sending]);

  useEffect(() => {
    if (
      not(isNil(defaultSelectedTimePeriodId)) ||
      isNil(defaultSelectedCustomTimePeriod) ||
      (equals(defaultSelectedCustomTimePeriod.start, customTimePeriod.start) &&
        equals(defaultSelectedCustomTimePeriod.end, customTimePeriod.end))
    ) {
      return;
    }

    const newCustomTimePeriod = getNewCustomTimePeriod({
      end: new Date(propOr(0, 'end', defaultSelectedCustomTimePeriod)),
      start: new Date(propOr(0, 'start', defaultSelectedCustomTimePeriod))
    });

    setCustomTimePeriod(newCustomTimePeriod);
    setSelectedTimePeriod(null);
  }, [defaultSelectedCustomTimePeriod]);

  useEffect(() => {
    if (
      isNil(defaultSelectedTimePeriodId) ||
      equals(defaultSelectedTimePeriodId, selectedTimePeriod?.id)
    ) {
      return;
    }

    const newTimePeriod = getTimePeriodById(
      defaultSelectedTimePeriodId as TimePeriodId
    );

    setSelectedTimePeriod(newTimePeriod);
  }, [defaultSelectedTimePeriodId]);
};

export default useTimePeriod;
