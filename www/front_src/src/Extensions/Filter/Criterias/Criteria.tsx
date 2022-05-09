import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import {
  PopoverMultiAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import {
  filterWithParsedSearchDerivedAtom,
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

  const changeCriteria = (upToDateValue): void => {
    setFilterCriteria({ name, value: upToDateValue });
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
  const translatedOptions = getTranslated(options);

  return (
    <PopoverMultiAutocompleteField
      {...commonProps}
      hideInput
      options={translatedOptions}
      value={translatedValues}
      onChange={(_, upToDateValue): void => {
        changeCriteria(getUntranslated(upToDateValue));
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
