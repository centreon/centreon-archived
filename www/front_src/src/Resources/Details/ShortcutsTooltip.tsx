import * as React from 'react';

import { isNil, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import MoreHorizIcon from '@mui/icons-material/MoreHoriz';
import LogsIcon from '@mui/icons-material/Assignment';
import ReportIcon from '@mui/icons-material/Assessment';
import {
  Link,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  Tooltip,
} from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

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
      name: labelViewLogs,
      uri: prop('logs', resourceUris),
    },
    {
      Icon: ReportIcon,
      name: labelViewReport,
      uri: prop('reporting', resourceUris),
    },
  ];

  return (
    <PopoverMenu
      icon={<MoreHorizIcon data-testid={t(labelShortcuts)} fontSize="small" />}
      title={t(labelShortcuts)}
    >
      {(): JSX.Element => (
        <List dense>
          {shortcuts.map(({ Icon, uri, name }) => (
            <Tooltip
              key={name}
              title={isNil(uri) ? (t(labelActionNotPermitted) as string) : ''}
            >
              <div data-testid={t(name)}>
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
