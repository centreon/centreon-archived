import * as React from 'react';

import { SelectEntry, SelectField } from '@centreon/ui';

import memoizeComponent from '../../memoizedComponent';

interface Props {
  ariaLabel: string;
  className: string;
  onChange: (event) => void;
  options: Array<SelectEntry>;
  selectedOptionId: string | number;
}

const SelectFilter = ({
  options,
  selectedOptionId,
  onChange,
  ariaLabel,
  className,
}: Props): JSX.Element => (
  <SelectField
    aria-label={ariaLabel}
    className={className}
    options={options}
    selectedOptionId={selectedOptionId}
    onChange={onChange}
  />
);

const memoProps = ['options', 'selectedOptionId'];

const MemoizedSelectFilter = memoizeComponent<Props>({
  Component: SelectFilter,
  memoProps,
});

export default MemoizedSelectFilter;
