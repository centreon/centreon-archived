import { useAtomValue } from 'jotai/utils';

import { platformVersionsAtom } from '../../../Main/atoms/platformVersionsAtom';

import {
  criteriaValueNameById,
  selectableResourceTypes,
  selectableCriterias,
  CriteriaNames,
  authorizedFilterByModules,
  CriteriaById,
} from './models';

const useFilterByModule = (): any => {
  const platformVersions = useAtomValue(platformVersionsAtom);

  const installedModules = platformVersions?.modules
    ? Object.keys(platformVersions?.modules)
    : null;

  const defaultFiltersByModules = Object.keys(authorizedFilterByModules);

  const filtersToAdd = defaultFiltersByModules.map((filterName) => {
    if (installedModules?.includes(filterName)) {
      return authorizedFilterByModules[filterName];
    }

    return null;
  });

  let newSelectableResourceTypes = [...selectableResourceTypes];
  let newCriteriaValueNameById = { ...criteriaValueNameById };

  const filters = filtersToAdd.map((item): any => {
    if (item) {
      Object.keys(item).map((key, ind) => {
        newCriteriaValueNameById = {
          ...newCriteriaValueNameById,
          [key]: Object.values(item)[ind],
        };

        const serviceId = key;
        const serviceType = {
          id: serviceId,
          name: newCriteriaValueNameById[serviceId],
        };

        newSelectableResourceTypes = [
          ...newSelectableResourceTypes,
          serviceType,
        ];

        return newSelectableResourceTypes;
      });
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
