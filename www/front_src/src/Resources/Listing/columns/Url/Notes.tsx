import * as React from 'react';

import { path } from 'ramda';

import IconLink from '@material-ui/icons/Link';

import { ComponentColumnProps } from '@centreon/ui';

import UrlColumn from '.';

const NotesUrlColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  const endpoint = path<string | undefined>(
    ['links', 'externals', 'notes', 'url'],
    row,
  );

  const title = path<string | undefined>(
    ['links', 'externals', 'notes', 'label'],
    row,
  );

  return (
    <UrlColumn
      endpoint={endpoint}
      title={title || endpoint}
      icon={<IconLink fontSize="small" />}
    />
  );
};

export default NotesUrlColumn;
