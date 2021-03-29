import * as React from 'react';

import { Typography } from '@material-ui/core';

interface Props {
  name: string;
  onSelect: () => void;
}

const SelectableResourceName = ({ name, onSelect }: Props): JSX.Element => {
  return (
    <Typography
      variant="body1"
      onClick={onSelect}
      style={{ cursor: 'pointer' }}
    >
      {name}
    </Typography>
  );
};

export default SelectableResourceName;
