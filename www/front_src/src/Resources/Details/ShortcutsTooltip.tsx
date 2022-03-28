import * as React from 'react';

import { isNil, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import MoreHorizIcon from '@material-ui/icons/MoreHoriz';
import LogsIcon from '@material-ui/icons/Assignment';
import ReportIcon from '@material-ui/icons/Assessment';
import {
  Link,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  makeStyles,
  Tooltip,
} from '@material-ui/core';

import { PopoverMenu } from '@centreon/ui';

import { ResourceUris } from '../models';
import {
  labelActionNotPermitted,
  labelShortcuts,
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
  const { t } = useTranslation();

  const shortcuts = [
    {
      Icon: LogsIcon,
      id: 'Logs',
      name: labelViewLogs,
      uri: prop('logs', resourceUris),
    },
    {
      Icon: ReportIcon,
      id: 'Reporting',
      name: labelViewReport,
      uri: prop('reporting', resourceUris),
    },
  ];

  return (
    <PopoverMenu
      icon={<MoreHorizIcon fontSize="small" />}
      title={t(labelShortcuts)}
    >
      {(): JSX.Element => (
        <List dense>
          {shortcuts.map(({ Icon, uri, name, id }) => (
            <Tooltip
              key={name}
              title={isNil(uri) ? (t(labelActionNotPermitted) as string) : ''}
            >
              <div data-testid={id}>
                <Link
                  aria-label={t(name)}
                  className={classes.link}
                  color="inherit"
                  href={uri}
                >
                  <ListItem button disabled={isNil(uri)}>
                    <ListItemIcon className={classes.iconContainer}>
                      <Icon color={isNil(uri) ? 'disabled' : 'primary'} />
                    </ListItemIcon>
                    <ListItemText>{t(name)}</ListItemText>
                  </ListItem>
                </Link>
              </div>
            </Tooltip>
          ))}
        </List>
      )}
    </PopoverMenu>
  );
};

export default ShortcutsTooltip;
