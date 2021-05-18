import * as React from 'react';

import { useTranslation } from 'react-i18next';

import SyncDisabledIcon from '@material-ui/icons/SyncDisabled';
import SyncProblemIcon from '@material-ui/icons/SyncProblem';
import { Tooltip } from '@material-ui/core';

import { Resource } from './models';
import {
  labelChecksDisabled,
  labelOnlyPassiveChecksEnabled,
} from './translatedLabels';

interface IconProps {
  Component: (props) => JSX.Element;
  title: string;
}

const Icon = ({ Component, title }: IconProps): JSX.Element => {
  const { t } = useTranslation();

  return (
    <Tooltip title={t(title) as string}>
      <Component color="primary" fontSize="small" />
    </Tooltip>
  );
};

type Props = Pick<Resource, 'active_checks' | 'passive_checks'>;

const ChecksIcon = ({
  active_checks,
  passive_checks,
}: Props): JSX.Element | null => {
  if (passive_checks === false && active_checks === false) {
    return <Icon Component={SyncDisabledIcon} title={labelChecksDisabled} />;
  }

  if (active_checks === false) {
    return (
      <Icon Component={SyncProblemIcon} title={labelOnlyPassiveChecksEnabled} />
    );
  }

  return null;
};

export default ChecksIcon;
