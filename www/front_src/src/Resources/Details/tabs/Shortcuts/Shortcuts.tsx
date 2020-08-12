import * as React from 'react';

import clsx from 'clsx';

import { makeStyles, Paper, Link } from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import LogsIcon from '@material-ui/icons/Assignment';
import ReportIcon from '@material-ui/icons/Assessment';
import { prop, pipe, isNil, not, filter } from 'ramda';
import { ResourceUris } from '../../../models';
import {
  labelConfigure,
  labelViewLogs,
  labelViewReport,
} from '../../../translatedLabels';

const useStyles = makeStyles((theme) => {
  return {
    gridWithSpacing: {
      display: 'grid',
      gridGap: theme.spacing(1),
      padding: theme.spacing(1),
    },
    shortcutRow: {
      gridAutoFlow: 'column',
      gridTemplateColumns: 'auto auto',
      alignItems: 'center',
      justifyContent: 'flex-start',
      gridGap: theme.spacing(2),
    },
  };
});

interface Props {
  uris: ResourceUris;
}

const Shortcuts = ({ uris }: Props): JSX.Element => {
  const classes = useStyles();

  const shortcuts = [
    {
      Icon: SettingsIcon,
      uri: prop('configuration', uris),
      name: labelConfigure,
    },
    {
      Icon: LogsIcon,
      uri: prop('logs', uris),
      name: labelViewLogs,
    },
    {
      Icon: ReportIcon,
      uri: prop('reporting', uris),
      name: labelViewReport,
    },
  ];

  const availableShortcuts = filter(pipe(prop('uri'), isNil, not), shortcuts);

  return (
    <Paper className={clsx([classes.gridWithSpacing])}>
      {availableShortcuts.map(({ Icon, uri, name }) => {
        return (
          <div
            key={name}
            className={clsx([classes.gridWithSpacing, classes.shortcutRow])}
          >
            <Icon color="primary" />
            <Link href={uri} color="inherit">
              {name}
            </Link>
          </div>
        );
      })}
    </Paper>
  );
};

export default Shortcuts;
