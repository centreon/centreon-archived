import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';

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
import { selectedResourceIdAtom } from '../../Details/detailsAtoms';

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
      size="small"
      title={t(label)}
      onClick={toggleAutorefresh}
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
  'enabledAutorefresh' | 'setEnabledAutorefresh' | 'sending'
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
          ariaLabel={t(labelRefresh)}
          disabled={sending}
          size="small"
          title={t(labelRefresh)}
          onClick={onRefresh}
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
  const selectedResourceId = useAtomValue(selectedResourceIdAtom);

  const { enabledAutorefresh, setEnabledAutorefresh, sending } =
    useResourceContext();

  return useMemoComponent({
    Component: (
      <RefreshActionsContent
        enabledAutorefresh={enabledAutorefresh}
        sending={sending}
        setEnabledAutorefresh={setEnabledAutorefresh}
        onRefresh={onRefresh}
      />
    ),
    memoProps: [sending, enabledAutorefresh, selectedResourceId],
  });
};

export default RefreshActions;
