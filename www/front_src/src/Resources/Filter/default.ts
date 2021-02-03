import {
  hasPath,
  indexBy,
  map,
  merge,
  mergeRight,
  mergeWith,
  pipe,
  prop,
  reduce,
  values,
} from 'ramda';

import { getUrlQueryParameters } from '@centreon/ui';

import {
  allFilter,
  newFilter,
  unhandledProblemsFilter,
  Filter,
} from './models';
import { getStoredOrDefaultFilter } from './storedFilter';
import { Criteria } from './Criterias/models';

const getDefaultFilter = (): Filter => {
  const defaultFilter = getStoredOrDefaultFilter(unhandledProblemsFilter);

  const urlQueryParameters = getUrlQueryParameters();

  if (hasPath(['filter'], urlQueryParameters)) {
    const filterFromUrl = urlQueryParameters.filter as Filter;

    const mergedCriterias = pipe(
      map(indexBy<Criteria>(prop('name'))),
      reduce(mergeWith(merge), {}),
      values,
    )([allFilter.criterias, filterFromUrl.criterias]);

    return {
      ...mergeRight(newFilter, filterFromUrl),
      criterias: mergedCriterias,
    };
  }

  return defaultFilter;
};

export default getDefaultFilter;
