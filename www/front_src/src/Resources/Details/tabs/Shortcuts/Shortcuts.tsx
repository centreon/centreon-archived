import * as React from 'react';

import clsx from 'clsx';
import { useTranslation } from 'react-i18next';
import { prop, pipe, isNil, not, filter } from 'ramda';

import { makeStyles, Paper, Link } from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import LogsIcon from '@material-ui/icons/Assignment';
import ReportIcon from '@material-ui/icons/Assessment';

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
      alignItems: 'center',
      gridAutoFlow: 'column',
      gridGap: theme.spacing(2),
      gridTemplateColumns: 'auto auto',
      justifyContent: 'flex-start',
    },
  };
});

interface Props {
  uris: ResourceUris;
}

const Shortcuts = ({ uris }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const shortcuts = [
    {
      Icon: SettingsIcon,
      name: labelConfigure,
      uri: prop('configuration', uris),
    },
    {
      Icon: LogsIcon,
      name: labelViewLogs,
      uri: prop('logs', uris),
    },
    {
      Icon: ReportIcon,
      name: labelViewReport,
      uri: prop('reporting', uris),
    },
  ];

  const availableShortcuts = filter(pipe(prop('uri'), isNil, not), shortcuts);

  return (
    <Paper className={clsx([classes.gridWithSpacing])}>
      {availableShortcuts.map(({ Icon, uri, name }) => {
        return (
          <div
            className={clsx([classes.gridWithSpacing, classes.shortcutRow])}
            key={name}
          >
            <Icon color="primary" />
            <Link color="inherit" href={uri as string} variant="body1">
              {t(name)}
            </Link>
          </div>
        );
      })}
    </Paper>
  );
};

export default Shortcuts;
