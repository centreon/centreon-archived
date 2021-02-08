import * as React from 'react';

import { MultiAutocompleteField } from '@centreon/ui';

import memoizeComponent from '../../memoizedComponent';
import { CriteriaValue } from '../models';

interface Props {
  options: Array<CriteriaValue>;
  label: string;
  onChange: (_, updatedValues) => void;
  value: Array<CriteriaValue>;
  openText: string;
}

const FilterAutocomplete = ({
  options,
  label,
  onChange,
  value,
  openText,
  ...commonProps
}: Props): JSX.Element => (
  <MultiAutocompleteField
    options={options}
    label={label}
    onChange={onChange}
    value={value || []}
    openText={openText}
    {...commonProps}
  />
);

const MemoizedFilterAutocomplete = memoizeComponent<Props>({
  memoProps: ['value'],
  Component: FilterAutocomplete,
});

export default MemoizedFilterAutocomplete;
