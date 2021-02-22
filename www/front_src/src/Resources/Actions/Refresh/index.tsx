import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Grid } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';
import IconPlay from '@material-ui/icons/PlayArrow';
import IconPause from '@material-ui/icons/Pause';

import { IconButton, useMemoComponent } from '@centreon/ui';

import {
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from '../../translatedLabels';
import { ResourceContext, useResourceContext } from '../../Context';

interface AutorefreshProps {
  enabledAutorefresh: boolean;
  toggleAutorefresh: () => void;
}

const AutorefreshButton = ({
  enabledAutorefresh,
  toggleAutorefresh,
}: AutorefreshProps): JSX.Element => {
  const { t } = useTranslation();

  const label = enabledAutorefresh
    ? labelDisableAutorefresh
    : labelEnableAutorefresh;

  return (
    <IconButton
      ariaLabel={t(label)}
      title={t(label)}
      onClick={toggleAutorefresh}
      size="small"
    >
      {enabledAutorefresh ? <IconPause /> : <IconPlay />}
    </IconButton>
  );
};

export interface ActionsProps {
  onRefresh: () => void;
}

type ResourceContextProps = Pick<
  ResourceContext,
  | 'enabledAutorefresh'
  | 'setEnabledAutorefresh'
  | 'sending'
  | 'selectedResourceId'
>;

const RefreshActionsContent = ({
  onRefresh,
  enabledAutorefresh,
  setEnabledAutorefresh,
  sending,
}: ActionsProps & ResourceContextProps): JSX.Element => {
  const { t } = useTranslation();

  const toggleAutorefresh = (): void => {
    setEnabledAutorefresh(!enabledAutorefresh);
  };

  return (
    <Grid container spacing={1}>
      <Grid item>
        <IconButton
          title={t(labelRefresh)}
          ariaLabel={t(labelRefresh)}
          disabled={sending}
          onClick={onRefresh}
          size="small"
        >
          <IconRefresh />
        </IconButton>
      </Grid>
      <Grid item>
        <AutorefreshButton
          enabledAutorefresh={enabledAutorefresh}
          toggleAutorefresh={toggleAutorefresh}
        />
      </Grid>
    </Grid>
  );
};

const RefreshActions = ({ onRefresh }: ActionsProps): JSX.Element => {
  const {
    enabledAutorefresh,
    setEnabledAutorefresh,
    sending,
    selectedResourceId,
  } = useResourceContext();

  return useMemoComponent({
    Component: (
      <RefreshActionsContent
        onRefresh={onRefresh}
        enabledAutorefresh={enabledAutorefresh}
        setEnabledAutorefresh={setEnabledAutorefresh}
        sending={sending}
      />
    ),
    memoProps: [sending, enabledAutorefresh, selectedResourceId],
  });
};

export default RefreshActions;
