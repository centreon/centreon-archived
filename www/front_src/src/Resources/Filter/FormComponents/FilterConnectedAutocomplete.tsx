import * as React from 'react';

import { MultiConnectedAutocompleteField } from '@centreon/ui';

import memoizeComponent from '../../memoizedComponent';
import { CriteriaValue } from '../models';

interface Props {
  getEndpoint: ({ search, page }) => string;
  label: string;
  onChange: (_, updatedValues) => void;
  value: Array<CriteriaValue>;
  openText: string;
  field: string;
}

const FilterConnectedAutocomplete = ({
  getEndpoint,
  label,
  onChange,
  value,
  openText,
  field,
  ...commonProps
}: Props): JSX.Element => (
  <MultiConnectedAutocompleteField
    getEndpoint={getEndpoint}
    label={label}
    onChange={onChange}
    value={value || []}
    openText={openText}
    field={field}
    {...commonProps}
  />
);

const MemoizedFilterConnectedAutocomplete = memoizeComponent<Props>({
  memoProps: ['value'],
  Component: FilterConnectedAutocomplete,
});

export default MemoizedFilterConnectedAutocomplete;
