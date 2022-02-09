import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { equals, isNil } from 'ramda';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import {
  PopoverMultiAutocompleteField,
  PopoverMultiConnectedAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import {
  currentFilterCriteriasAtom,
  setFilterCriteriaDerivedAtom,
} from '../filterAtoms';

import { criteriaValueNameById, selectableCriterias } from './models';

interface Props {
  name: string;
  value: Array<SelectEntry>;
}

const CriteriaContent = ({ name, value }: Props): JSX.Element => {
  const { t } = useTranslation();

  const setFilterCriteria = useUpdateAtom(setFilterCriteriaDerivedAtom);

  const getTranslated = (values: Array<SelectEntry>): Array<SelectEntry> => {
    return values.map((entry) => ({
      id: entry.id,
      name: t(entry.name),
    }));
  };

  const changeCriteria = (updatedValue): void => {
    setFilterCriteria({ name, value: updatedValue });
  };

  const getUntranslated = (values): Array<SelectEntry> => {
    return values.map(({ id }) => ({
      id,
      name: criteriaValueNameById[id],
    }));
  };

  const { label, options } = selectableCriterias[name];

  const commonProps = {
    label: t(label),
  };

  if (isNil(options)) {
    const isOptionEqualToValue = (option, selectedValue): boolean =>
      equals(option.name, selectedValue.name);

    //   buildAutocompleteEndpoint({
    //     limit: 10,
    //     page,
    //     search,
    //   });

    return (
      <PopoverMultiConnectedAutocompleteField
        {...commonProps}
        disableSortedOptions
        field="name"
        // getEndpoint={getEndpoint}
        isOptionEqualToValue={isOptionEqualToValue}
        value={value}
        onChange={(_, updatedValue): void => {
          changeCriteria(updatedValue);
        }}
      />
    );
  }

  const translatedValues = getTranslated(value);
  const translatedOptions = getTranslated(options);

  return (
    <PopoverMultiAutocompleteField
      {...commonProps}
      hideInput
      options={translatedOptions}
      value={translatedValues}
      onChange={(_, updatedValue): void => {
        changeCriteria(getUntranslated(updatedValue));
      }}
    />
  );
};

const Criteria = ({ value, name }: Props): JSX.Element => {
  const etCurrentFilterCriterias = useAtomValue(currentFilterCriteriasAtom);

  return useMemoComponent({
    Component: <CriteriaContent name={name} value={value} />,
    memoProps: [value, name, etCurrentFilterCriterias],
  });
};

export default Criteria;
