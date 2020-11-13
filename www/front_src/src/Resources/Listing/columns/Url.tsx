import * as React from 'react';

import IconLink from '@material-ui/icons/Link';

import { IconButton, ComponentColumnProps } from '@centreon/ui';

import { path, isNil, isEmpty } from 'ramda';
import { labelUrl } from '../../translatedLabels';

const UrlColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  const endpoint = path<string | undefined>(
    ['links', 'externals', 'notes_url'],
    row,
  );

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
        title={labelUrl}
        ariaLabel={labelUrl}
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
