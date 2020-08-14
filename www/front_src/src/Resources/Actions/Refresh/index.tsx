import * as React from 'react';

import { Grid } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';
import IconPlay from '@material-ui/icons/PlayArrow';
import IconPause from '@material-ui/icons/Pause';

import { IconButton } from '@centreon/ui';

import {
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from '../../translatedLabels';
import { useResourceContext } from '../../Context';

interface AutorefreshProps {
  enabledAutorefresh: boolean;
  toggleAutorefresh: () => void;
}

const AutorefreshButton = ({
  enabledAutorefresh,
  toggleAutorefresh,
}: AutorefreshProps): JSX.Element => {
  const label = enabledAutorefresh
    ? labelDisableAutorefresh
    : labelEnableAutorefresh;

  return (
    <IconButton
      ariaLabel={label}
      title={label}
      onClick={toggleAutorefresh}
      size="small"
    >
      {enabledAutorefresh ? <IconPause /> : <IconPlay />}
    </IconButton>
  );
};

interface Props {
  onRefresh: () => void;
}

const RefreshActions = ({ onRefresh }: Props): JSX.Element => {
  const {
    enabledAutorefresh,
    setEnabledAutorefresh,
    sending,
  } = useResourceContext();

  const toggleAutorefresh = (): void => {
    setEnabledAutorefresh(!enabledAutorefresh);
  };

  return (
    <Grid container spacing={1}>
      <Grid item>
        <IconButton
          title={labelRefresh}
          ariaLabel={labelRefresh}
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

export default RefreshActions;
