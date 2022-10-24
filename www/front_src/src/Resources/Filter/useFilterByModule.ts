import { useAtomValue } from 'jotai/utils';

import { SelectEntry } from '@centreon/ui';

import { platformVersionsAtom } from '../../Main/atoms/platformVersionsAtom';

import {
  authorizedFilterByModules,
  CriteriaById,
  CriteriaNames,
  criteriaValueNameById,
  selectableCriterias,
  selectableResourceTypes
} from './Criterias/models';

interface FilterByModule {
  newCriteriaValueName: Record<string, string>;
  newSelectableCriterias: CriteriaById;
}

const useFilterByModule = (): FilterByModule => {
  const platformVersions = useAtomValue(platformVersionsAtom);

  const installedModules = platformVersions?.modules
    ? Object.keys(platformVersions?.modules)
    : null;

  const defaultFiltersByModules = Object.keys(authorizedFilterByModules);

  const filtersToAdd = defaultFiltersByModules.map((filterName) => {
    if (!installedModules?.includes(filterName)) {
      return null;
    }

    return authorizedFilterByModules[filterName];
  });

  const newCriteriaValueNameById = filtersToAdd.reduce(
    (prev, item) => {
      if (!item) {
        return { ...prev };
      }

      const criteriasNameById = Object.keys(item).reduce(
        (previousValue, key) => ({
          ...previousValue,
          [key]: item[key]
        }),
        { ...criteriaValueNameById }
      );

      return { ...prev, ...criteriasNameById };
    },
    { ...criteriaValueNameById }
  );

  const newSelectableResourceTypes = filtersToAdd.reduce(
    (prev, item) => {
      if (!item) {
        return [...prev];
      }

      const selectableTypes = Object.keys(item).reduce(
        (previousValue, key) => {
          const serviceType = {
            id: key,
            name: newCriteriaValueNameById[key]
          };

          return [...previousValue, serviceType];
        },
        [...selectableResourceTypes]
      );

      return [...prev, ...selectableTypes];
    },
    [...selectableResourceTypes]
  );

  const newSelectableCriterias = {
    ...selectableCriterias,
    [CriteriaNames.resourceTypes]: {
      ...selectableCriterias[CriteriaNames.resourceTypes],
      options: [...new Set(newSelectableResourceTypes)] as Array<SelectEntry>
    }
  };

  return {
    newCriteriaValueName: newCriteriaValueNameById,
    newSelectableCriterias
  };
};

export default useFilterByModule;
