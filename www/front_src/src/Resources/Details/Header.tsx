/* eslint-disable hooks/sort */
// Issue : https://github.com/hiukky/eslint-plugin-hooks/issues/3

import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { hasPath, isNil, not, path, prop } from 'ramda';

import { Grid, Typography, Theme, Link, Tooltip } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import { Skeleton } from '@mui/material';
import CopyIcon from '@mui/icons-material/FileCopy';
import SettingsIcon from '@mui/icons-material/Settings';
import { CreateCSSProperties } from '@mui/styles';

import {
  StatusChip,
  SeverityCode,
  IconButton,
  useSnackbar,
  copyToClipboard,
} from '@centreon/ui';

import {
  labelActionNotPermitted,
  labelConfigure,
  labelCopyLink,
  labelLinkCopied,
  labelSomethingWentWrong,
} from '../translatedLabels';
import { Parent, ResourceUris } from '../models';

import SelectableResourceName from './tabs/Details/SelectableResourceName';
import ShortcutsTooltip from './ShortcutsTooltip';

import { DetailsSectionProps } from '.';

interface MakeStylesProps {
  displaySeverity: boolean;
}

const useStyles = makeStyles<Theme, MakeStylesProps>((theme) => ({
  header: ({ displaySeverity }): CreateCSSProperties<MakeStylesProps> => ({
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: `${
      displaySeverity ? 'auto' : ''
    } auto minmax(0, 1fr) auto auto`,
    height: 43,
    padding: theme.spacing(0, 1),
  }),
  parent: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto minmax(0, 1fr)',
  },
  resourceName: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: 'minmax(auto, min-content) min-content',
    height: '100%',
  },
  resourceNameConfigurationIcon: {
    alignSelf: 'center',
    display: 'flex',
    minWidth: theme.spacing(2.5),
  },
  resourceNameConfigurationLink: {
    height: theme.spacing(2.5),
  },
  resourceNameContainer: {
    display: 'flex',
    flexDirection: 'column',
    height: '100%',
    width: '100%',
  },
  resourceNameTooltip: {
    maxWidth: 'none',
  },
  truncated: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container item alignItems="center" spacing={2} style={{ flexGrow: 1 }}>
    <Grid item>
      <Skeleton height={25} variant="circular" width={25} />
    </Grid>
    <Grid item>
      <Skeleton height={25} width={250} />
    </Grid>
  </Grid>
);

type Props = {
  onSelectParent: (parent: Parent) => void;
} & DetailsSectionProps;

const Header = ({ details, onSelectParent }: Props): JSX.Element => {
  const { t } = useTranslation();
  const { showSuccessMessage, showErrorMessage } = useSnackbar();
  const classes = useStyles({
    displaySeverity: not(isNil(details?.severity_level)),
  });

  const copyResourceLink = (): void => {
    try {
      copyToClipboard(window.location.href);
      showSuccessMessage(t(labelLinkCopied));
    } catch (_) {
      showErrorMessage(t(labelSomethingWentWrong));
    }
  };

  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  const resourceUris = path<ResourceUris>(
    ['links', 'uris'],
    details,
  ) as ResourceUris;

  const resourceConfigurationUri = prop('configuration', resourceUris);

  const resourceConfigurationUriTitle = isNil(resourceConfigurationUri)
    ? t(labelActionNotPermitted)
    : '';

  const resourceConfigurationIconColor = isNil(resourceConfigurationUri)
    ? 'disabled'
    : 'primary';

  return (
    <div className={classes.header}>
      {details?.severity_level && (
        <StatusChip
          label={details?.severity_level.toString()}
          severityCode={SeverityCode.None}
        />
      )}
      <StatusChip
        label={t(details.status.name)}
        severityCode={details.status.severity_code}
      />
      <div className={classes.resourceNameContainer}>
        <div
          aria-label={`${details.name}_hover`}
          className={classes.resourceName}
        >
          <Tooltip
            classes={{ tooltip: classes.resourceNameTooltip }}
            placement="top"
            title={details.name}
          >
            <Typography className={classes.truncated}>
              {details.name}
            </Typography>
          </Tooltip>
          <Tooltip title={resourceConfigurationUriTitle}>
            <div className={classes.resourceNameConfigurationIcon}>
              <Link
                aria-label={`${t(labelConfigure)}_${details.name}`}
                className={classes.resourceNameConfigurationLink}
                href={resourceConfigurationUri}
              >
                <SettingsIcon
                  color={resourceConfigurationIconColor}
                  fontSize="small"
                />
              </Link>
            </div>
          </Tooltip>
        </div>
        {hasPath(['parent', 'status'], details) && (
          <div className={classes.parent}>
            <StatusChip
              severityCode={
                details.parent.status?.severity_code || SeverityCode.None
              }
            />
            <SelectableResourceName
              name={details.parent.name}
              variant="caption"
              onSelect={(): void => onSelectParent(details.parent)}
            />
          </div>
        )}
      </div>
      <ShortcutsTooltip resourceUris={resourceUris} />
      <IconButton
        ariaLabel={t(labelCopyLink)}
        size="small"
        title={t(labelCopyLink)}
        onClick={copyResourceLink}
      >
        <CopyIcon fontSize="small" />
      </IconButton>
    </div>
  );
};

export default Header;
