import { useAtomValue } from 'jotai/utils';

import { platformVersionsAtom } from '../../../Main/atoms/platformVersionsAtom';

import {
  criteriaFilterByModules,
  criteriaValueNameById,
  selectableResourceTypes,
  selectableCriterias,
  CriteriaNames,
  CriteriaById,
} from './models';

const useFilterByModule = (): any => {
  const platformVersions = useAtomValue(platformVersionsAtom);
  const Base = 'centreon-';

  const installedModules = platformVersions?.modules
    ? Object.keys(platformVersions?.modules)
    : null;

  const names = installedModules?.map((item) => item.replace(Base, ''));

  const defaultFiltersByModules = Object.keys(criteriaFilterByModules);

  const filtersToAdd = defaultFiltersByModules.map((filterName) => {
    if (names?.includes(filterName)) {
      return filterName;
    }

    return null;
  });

  let newSelectableResourceTypes = [...selectableResourceTypes];
  const filters = filtersToAdd.map((item): any => {
    if (item) {
      const newCriteriaValueNameById = {
        ...criteriaValueNameById,
        [item]: criteriaFilterByModules[item],
      };

      const serviceId = item;
      const serviceType = {
        id: serviceId,
        name: newCriteriaValueNameById[serviceId],
      };

      newSelectableResourceTypes = [...newSelectableResourceTypes, serviceType];
    }

    return newSelectableResourceTypes;
  });

  const newOptions = filters[filters.length - 1];

  const newSelectableCriterias = {
    ...selectableCriterias,
    [CriteriaNames.resourceTypes]: {
      ...selectableCriterias[CriteriaNames.resourceTypes],
      options: newOptions,
    },
  };

  return { newSelectableCriterias };
};

export default useFilterByModule;
