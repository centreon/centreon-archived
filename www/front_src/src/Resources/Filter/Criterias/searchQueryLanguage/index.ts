import {
  any,
  find,
  flip,
  head,
  includes,
  keys,
  last,
  map,
  none,
  not,
  partition,
  pipe,
  prop,
  split,
} from 'ramda';

import { SelectEntry } from '@centreon/centreon-ui';

import { selectableCriterias } from '../models';

const buildOptions = (options: Array<SelectEntry>) => {
  return options.map(prop('id')).join(',');
};

const parse = (search: string) => {
  const criteriaKeys = keys(selectableCriterias) as Array<string>;

  const parts = search.split(' ');

  const isIn = flip(includes);

  const isPrefixInCriterias = pipe(split(':'), head, isIn(criteriaKeys));

  const partie = partition(isPrefixInCriterias, parts);

  const criterias = head(partie);

  criterias.map((criteria) => {
    const [key, value] = criteria.split(':');

    const values = value.split(',');
    const retrievedCriteria = selectableCriterias[key];
    const optionIds = retrievedCriteria.options.map<string>(prop('id'));

    const isNotInOptions = (val: string) => !includes(val, optionIds);

    if (any(isNotInOptions)(values)) {
      return undefined;
    }

    return values;
  });
};

export { parse };
