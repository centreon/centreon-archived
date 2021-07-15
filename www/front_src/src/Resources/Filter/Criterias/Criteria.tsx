import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

import {
  PopoverMultiAutocompleteField,
  PopoverMultiConnectedAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import { ResourceContext, useResourceContext } from '../../Context';

import { criteriaValueNameById, selectableCriterias } from './models';

interface Props {
  name: string;
  value: Array<SelectEntry>;
}

const CriteriaContent = ({
  name,
  value,
  setCriteriaAndNewFilter,
}: Props & Pick<ResourceContext, 'setCriteriaAndNewFilter'>): JSX.Element => {
  const { t } = useTranslation();

  const getTranslated = (values: Array<SelectEntry>): Array<SelectEntry> => {
    return values.map((entry) => ({
      id: entry.id,
      name: t(entry.name),
    }));
  };

  const changeCriteria = (updatedValue): void => {
    setCriteriaAndNewFilter({ name, value: updatedValue });
  };

  const getUntranslated = (values): Array<SelectEntry> => {
    return values.map(({ id }) => ({
      id,
      name: criteriaValueNameById[id],
    }));
  };

  const { label, options, buildAutocompleteEndpoint, autocompleteSearch } =
    selectableCriterias[name];

  const commonProps = {
    label: t(label),
    search: autocompleteSearch,
  };

  if (isNil(options)) {
    const getEndpoint = ({ search, page }) =>
      buildAutocompleteEndpoint({
        limit: 10,
        page,
        search,
      });

    return (
      <PopoverMultiConnectedAutocompleteField
        {...commonProps}
        field="name"
        getEndpoint={getEndpoint}
        value={value}
        onChange={(_, updatedValue) => {
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
      options={translatedOptions}
      value={translatedValues}
      onChange={(_, updatedValue) => {
        changeCriteria(getUntranslated(updatedValue));
      }}
    />
  );
};

const Criteria = ({ value, name }: Props): JSX.Element => {
  const { setCriteriaAndNewFilter, filterWithParsedSearch } =
    useResourceContext();

  return useMemoComponent({
    Component: (
      <CriteriaContent
        name={name}
        setCriteriaAndNewFilter={setCriteriaAndNewFilter}
        value={value}
      />
    ),
    memoProps: [value, name, filterWithParsedSearch],
  });
};

export default Criteria;
