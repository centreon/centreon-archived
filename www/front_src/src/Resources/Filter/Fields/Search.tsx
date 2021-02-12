import * as React from 'react';

import { SearchField } from '@centreon/ui';

import memoizeComponent from '../../memoizedComponent';

interface Props {
  EndAdornment: () => JSX.Element;
  value?: string;
  onChange: (event) => void;
  placeholder: string;
  onKeyDown: (event: React.KeyboardEvent) => void;
}

const Search = ({
  EndAdornment,
  value,
  onChange,
  placeholder,
  onKeyDown,
}: Props): JSX.Element => (
  <SearchField
    EndAdornment={EndAdornment}
    value={value || ''}
    onChange={onChange}
    placeholder={placeholder}
    onKeyDown={onKeyDown}
  />
);

const memoProps = ['value'];

const MemoizedSearch = memoizeComponent<Props>({
  memoProps,
  Component: Search,
});

export default MemoizedSearch;
