import { useAtomValue } from 'jotai/utils';

import { platformVersionsAtom } from '../../../Main/atoms/platformVersionsAtom';

import {
  criteriaFilterByModules,
  criteriaValueNameById,
  selectableResourceTypes,
  selectableCriterias,
  CriteriaNames,
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
  let newCriteriaValueNameById = { ...criteriaValueNameById };

  const filters = filtersToAdd.map((item): any => {
    if (item) {
      newCriteriaValueNameById = {
        ...newCriteriaValueNameById,
        [item]: criteriaFilterByModules[item],
      };

      const serviceId = item;
      const serviceType = {
        id: serviceId,
        name: newCriteriaValueNameById[serviceId],
      };

      newSelectableResourceTypes = [...newSelectableResourceTypes, serviceType];
    }

    return { newCriteriaValueNameById, newSelectableResourceTypes };
  });

  const result = filters[filters.length - 1];

  const newSelectableCriterias = {
    ...selectableCriterias,
    [CriteriaNames.resourceTypes]: {
      ...selectableCriterias[CriteriaNames.resourceTypes],
      options: result?.newSelectableResourceTypes,
    },
  };

  return {
    newCriteriaValueName: result?.newCriteriaValueNameById,
    newSelectableCriterias,
  };
};

export default useFilterByModule;
