import { SelectEntry } from '@centreon/ui';

import { PartialUnitValueLimit, UnitValueLimit } from '../models';

const commonEntry = {
  id: 0,
  name: '0'
};

const getTimeInputOptions = ({
  max,
  min
}: UnitValueLimit): Array<SelectEntry> => [
  commonEntry,
  ...Array(max - min + 1)
    .fill(0)
    .map((_, index) => ({ id: min + index, name: `${min + index}` }))
];

export const getMonthsOptions = ({
  max = 12,
  min = 1
}: PartialUnitValueLimit): Array<SelectEntry> =>
  getTimeInputOptions({ max, min });

export const getDaysOptions = ({
  max = 30,
  min = 1
}: PartialUnitValueLimit): Array<SelectEntry> =>
  getTimeInputOptions({ max, min });

export const getHoursOptions = ({
  max = 23,
  min = 1
}: PartialUnitValueLimit): Array<SelectEntry> =>
  getTimeInputOptions({ max, min });

export const getMinutesOptions = ({
  max = 59,
  min = 1
}: PartialUnitValueLimit): Array<SelectEntry> =>
  getTimeInputOptions({ max, min });

export const getSecondsOptions = ({
  max = 59,
  min = 1
}: PartialUnitValueLimit): Array<SelectEntry> =>
  getTimeInputOptions({ max, min });
