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
  filterWithParsedSearchDerivedAtom,
  setCriteriaAndNewFilterDerivedAtom,
} from '../filterAtoms';
import useFilterByModule from '../useFilterByModule';

import { criteriaValueNameById } from './models';

interface Props {
  name: string;
  value: Array<SelectEntry>;
}

const CriteriaContent = ({ name, value }: Props): JSX.Element => {
  const { t } = useTranslation();
  const { newSelectableCriterias } = useFilterByModule();

  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

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
    newSelectableCriterias[name];

  const commonProps = {
    label: t(label),
    search: autocompleteSearch,
  };

  if (isNil(options)) {
    const isOptionEqualToValue = (option, selectedValue): boolean =>
      isNil(option)
        ? false
        : equals(option.name.toString(), selectedValue.name);

    const getEndpoint = ({ search, page }): string =>
      buildAutocompleteEndpoint({
        limit: 10,
        page,
        search,
      });

    const displayedColumn = label.includes('level') ? 'level' : '';

    return (
      <PopoverMultiConnectedAutocompleteField
        {...commonProps}
        disableSortedOptions
        field="name"
        getEndpoint={getEndpoint}
        isOptionEqualToValue={isOptionEqualToValue}
        labelKey={displayedColumn}
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
  const filterWithParsedSearch = useAtomValue(
    filterWithParsedSearchDerivedAtom,
  );

  return useMemoComponent({
    Component: <CriteriaContent name={name} value={value} />,
    memoProps: [value, name, filterWithParsedSearch],
  });
};

export default Criteria;
