import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import {
  PopoverMultiAutocompleteField,
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

  const translatedValues = getTranslated(value);

  return (
    <PopoverMultiAutocompleteField
      {...commonProps}
      hideInput
      options={options}
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
