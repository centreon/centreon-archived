import * as React from 'react';

import { path } from 'ramda';

import IconLink from '@material-ui/icons/Link';

import { ComponentColumnProps } from '@centreon/ui';

import UrlColumn from '.';

const NotesUrlColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  const endpoint = path<string | undefined>(
    ['links', 'externals', 'notes', 'notes_url'],
    row,
  );

  const title = path<string | undefined>(
    ['links', 'externals', 'notes', 'notes'],
    row,
  );

  return (
    <UrlColumn
      endpoint={endpoint}
      title={title}
      icon={<IconLink fontSize="small" />}
    />
  );
};

export default NotesUrlColumn;
