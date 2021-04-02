import * as React from 'react';

import { useTranslation } from 'react-i18next';

import SyncDisabledIcon from '@material-ui/icons/SyncDisabled';
import SyncProblemIcon from '@material-ui/icons/SyncProblem';
import { Tooltip } from '@material-ui/core';

import { ComponentColumnProps } from '@centreon/ui';

import {
  labelChecksDisabled,
  labelOnlyPassiveChecksEnabled,
} from '../../translatedLabels';

import IconColumn from './IconColumn';

interface ColumnProps {
  Icon: (props) => JSX.Element;
  title: string;
}

const Column = ({ Icon, title }: ColumnProps): JSX.Element => {
  return (
    <IconColumn>
      <Tooltip title={title}>
        <Icon color="primary" fontSize="small" />
      </Tooltip>
    </IconColumn>
  );
};

const ChecksColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  const { t } = useTranslation();

  if (row.passive_checks === false && row.active_checks === false) {
    return <Column Icon={SyncDisabledIcon} title={t(labelChecksDisabled)} />;
  }

  if (row.active_checks === false) {
    return (
      <Column Icon={SyncProblemIcon} title={t(labelOnlyPassiveChecksEnabled)} />
    );
  }

  return null;
};

export default ChecksColumn;
