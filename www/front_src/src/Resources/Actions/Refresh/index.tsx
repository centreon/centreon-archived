import * as React from 'react';

import { Grid } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';
import IconPlay from '@material-ui/icons/PlayArrow';
import IconPause from '@material-ui/icons/Pause';

import {
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from '../../translatedLabels';
import ActionButton from '../../ActionButton';
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
    <ActionButton
      ariaLabel={label}
      title={label}
      onClick={toggleAutorefresh}
      size="small"
    >
      {enabledAutorefresh ? <IconPause /> : <IconPlay />}
    </ActionButton>
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
        <ActionButton
          title={labelRefresh}
          ariaLabel={labelRefresh}
          disabled={sending}
          onClick={onRefresh}
          size="small"
        >
          <IconRefresh />
        </ActionButton>
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
