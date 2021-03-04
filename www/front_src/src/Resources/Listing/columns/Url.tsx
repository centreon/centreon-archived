import * as React from 'react';

import { path, isNil, isEmpty } from 'ramda';

import IconLink from '@material-ui/icons/Link';

import { IconButton, ComponentColumnProps } from '@centreon/ui';

const UrlColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  const notesPath = ['links', 'externals', 'notes'];

  const endpoint = path<string | undefined>([...notesPath, 'url'], row);
  const title = path<string | undefined>([...notesPath, 'label'], row);

  if (isNil(endpoint) || isEmpty(endpoint)) {
    return null;
  }

  return (
    <a
      href={endpoint}
      onClick={(e): void => {
        e.stopPropagation();
      }}
    >
      <IconButton
        title={title || endpoint}
        ariaLabel={title || endpoint}
        onClick={(): null => {
          return null;
        }}
      >
        <IconLink fontSize="small" />
      </IconButton>
    </a>
  );
};

export default UrlColumn;
