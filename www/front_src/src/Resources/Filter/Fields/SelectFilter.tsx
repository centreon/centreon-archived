import * as React from 'react';

import { SelectEntry, SelectField } from '@centreon/ui';

import memoizeComponent from '../../memoizedComponent';

interface Props {
  options: Array<SelectEntry>;
  selectedOptionId: string | number;
  onChange: (event) => void;
  ariaLabel: string;
  className: string;
}

const SelectFilter = ({
  options,
  selectedOptionId,
  onChange,
  ariaLabel,
  className,
}: Props): JSX.Element => (
  <SelectField
    options={options}
    selectedOptionId={selectedOptionId}
    onChange={onChange}
    aria-label={ariaLabel}
    className={className}
  />
);

const memoProps = ['options', 'selectedOptionId'];

const MemoizedSelectFilter = memoizeComponent<Props>({
  memoProps,
  Component: SelectFilter,
});

export default MemoizedSelectFilter;
