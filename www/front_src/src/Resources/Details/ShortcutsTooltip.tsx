import * as React from 'react';

import { filter, isNil, not, pipe, prop } from 'ramda';

import MoreHorizIcon from '@material-ui/icons/MoreHoriz';
import SettingsIcon from '@material-ui/icons/Settings';
import LogsIcon from '@material-ui/icons/Assignment';
import ReportIcon from '@material-ui/icons/Assessment';
import {
  Link,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  makeStyles,
} from '@material-ui/core';

import { PopoverMenu } from '@centreon/ui';

import { ResourceUris } from '../models';
import {
  labelConfigure,
  labelViewLogs,
  labelViewReport,
} from '../translatedLabels';

interface Props {
  resourceUris: ResourceUris;
}

const useStyles = makeStyles((theme) => ({
  iconContainer: {
    minWidth: theme.spacing(4.5),
  },
  link: {
    display: 'contents',
  },
}));

const ShortcutsTooltip = ({ resourceUris }: Props): JSX.Element => {
  const classes = useStyles();

  const shortcuts = [
    {
      Icon: SettingsIcon,
      name: labelConfigure,
      uri: prop('configuration', resourceUris),
    },
    {
      Icon: LogsIcon,
      name: labelViewLogs,
      uri: prop('logs', resourceUris),
    },
    {
      Icon: ReportIcon,
      name: labelViewReport,
      uri: prop('reporting', resourceUris),
    },
  ];

  const availableShortcuts = filter(pipe(prop('uri'), isNil, not), shortcuts);

  return (
    <PopoverMenu icon={<MoreHorizIcon fontSize="small" />}>
      <List dense>
        {availableShortcuts.map(({ Icon, uri, name }) => (
          <Link
            className={classes.link}
            color="inherit"
            href={uri as string}
            key={name}
          >
            <ListItem button>
              <ListItemIcon className={classes.iconContainer}>
                <Icon color="primary" />
              </ListItemIcon>
              <ListItemText>{name}</ListItemText>
            </ListItem>
          </Link>
        ))}
      </List>
    </PopoverMenu>
  );
};

export default ShortcutsTooltip;
