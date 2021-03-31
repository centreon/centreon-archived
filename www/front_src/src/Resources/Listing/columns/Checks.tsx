import * as React from 'react';

import SyncDisabledIcon from '@material-ui/icons/SyncDisabled';
import { Tooltip } from '@material-ui/core';

import { ComponentColumnProps } from '@centreon/ui';

import { labelChecksDisabled } from '../../translatedLabels';

import IconColumn from './IconColumn';

const ChecksColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  if (row.passive_checks === false && row.active_checks === false) {
    return (
      <IconColumn>
        <Tooltip title={labelChecksDisabled}>
          <SyncDisabledIcon color="primary" fontSize="small" />
        </Tooltip>
      </IconColumn>
    );
  }

  return null;
};

export default ChecksColumn;
