import { identity } from 'ramda';

import {
  labelEndDateGreaterThanStartDate,
  labelMaxDuration1Year,
} from '../../../../translatedLabels';

import { validate } from '.';

const t = identity;

describe(validate, () => {
  it('returns an error message when the given start date is grater than the start date', () => {
    const startDate = new Date('01/02/2020');
    const endDate = new Date('01/01/2020');

    const values = {
      dateEnd: endDate,
      dateStart: startDate,
      timeEnd: endDate,
      timeStart: startDate,
    };

    expect(validate({ t, values })).toEqual({
      dateEnd: labelEndDateGreaterThanStartDate,
    });
  });

  it('returns an error message when the given duration is grater than a year', () => {
    const startDate = new Date('01/01/2020');
    const endDate = new Date('01/01/2021');

    const values = {
      dateEnd: endDate,
      dateStart: startDate,
      timeEnd: endDate,
      timeStart: startDate,
    };

    expect(validate({ t, values })).toEqual({
      dateEnd: labelMaxDuration1Year,
    });
  });
});
