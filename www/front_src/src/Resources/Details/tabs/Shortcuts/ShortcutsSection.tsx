import * as React from 'react';

import { Typography } from '@material-ui/core';

import { ResourceUris } from '../../../models';
import Shortcuts from './Shortcuts';

interface Props {
  title: string;
  uris: ResourceUris;
}

const ShortcutsSection = ({ title, uris }: Props): JSX.Element => {
  return (
    <>
      <Typography variant="h6">{title}</Typography>
      <Shortcuts uris={uris} />
    </>
  );
};

export default ShortcutsSection;
