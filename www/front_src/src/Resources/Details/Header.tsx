import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { hasPath, isNil, not, path, prop } from 'ramda';

import { Grid, Typography, makeStyles, Theme, Link } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';
import CopyIcon from '@material-ui/icons/FileCopy';
import SettingsIcon from '@material-ui/icons/Settings';

import {
  StatusChip,
  SeverityCode,
  IconButton,
  useSnackbar,
  Severity,
  copyToClipboard,
} from '@centreon/ui';

import {
  labelConfigure,
  labelCopyLink,
  labelLinkCopied,
  labelSomethingWentWrong,
} from '../translatedLabels';
import memoizeComponent from '../memoizedComponent';
import { Parent, ResourceUris } from '../models';

import SelectableResourceName from './tabs/Details/SelectableResourceName';
import ShortcutsTooltip from './ShortcutsTooltip';

import { DetailsSectionProps } from '.';

interface MakeStylesProps {
  displaySeverity: boolean;
}

const useStyles = makeStyles<Theme, MakeStylesProps>((theme) => ({
  header: ({ displaySeverity }) => ({
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: `${
      displaySeverity ? 'auto' : ''
    } auto minmax(0, 1fr) auto auto`,
    height: 43,
    padding: theme.spacing(0, 1),
  }),
}));

const useStylesHeaderContent = makeStyles((theme) => ({
  parent: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto minmax(0, 1fr)',
  },
  resourceName: {
    columnGap: theme.spacing(0.5),
    display: 'grid',
    gridTemplateColumns: 'repeat(2, min-content)',
    height: theme.spacing(3),
    width: 'min-content',
  },
  resourceNameConfigurationIcon: {
    alignSelf: 'center',
    display: 'flex',
    minWidth: theme.spacing(3),
  },
  resourceNameConfigurationLink: {
    height: theme.spacing(2.5),
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
      <Skeleton height={25} variant="circle" width={25} />
    </Grid>
    <Grid item>
      <Skeleton height={25} width={250} />
    </Grid>
  </Grid>
);

type Props = {
  onSelectParent: (parent: Parent) => void;
} & DetailsSectionProps;

const HeaderContent = ({ details, onSelectParent }: Props): JSX.Element => {
  const [resourceNameHovered, setResourceNameHovered] = React.useState(false);
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();
  const classes = useStylesHeaderContent();

  const hoverResourceName = () => {
    setResourceNameHovered(true);
  };

  const leaveResourceName = () => {
    setResourceNameHovered(false);
  };

  const copyResourceLink = (): void => {
    try {
      copyToClipboard(window.location.href);
      showMessage({
        message: t(labelLinkCopied),
        severity: Severity.success,
      });
    } catch (_) {
      showMessage({
        message: t(labelSomethingWentWrong),
        severity: Severity.error,
      });
    }
  };

  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  const uris: ResourceUris = {
    configuration: undefined,
    logs: 'coucou',
    reporting: undefined,
  };

  const resourceUris = path<ResourceUris>(
    ['links', 'uris'],
    details,
  ) as ResourceUris;

  const resourceConfigurationUri = prop('configuration', uris);

  return (
    <>
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
      <div>
        <div
          aria-label={`${details.name}_hover`}
          className={classes.resourceName}
          onMouseEnter={hoverResourceName}
          onMouseLeave={leaveResourceName}
        >
          <Typography className={classes.truncated}>{details.name}</Typography>
          <div className={classes.resourceNameConfigurationIcon}>
            {resourceNameHovered && not(isNil(resourceConfigurationUri)) && (
              <Link
                aria-label={`${t(labelConfigure)}_${details.name}`}
                className={classes.resourceNameConfigurationLink}
                href={resourceConfigurationUri}
              >
                <SettingsIcon fontSize="small" />
              </Link>
            )}
          </div>
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
              onSelect={() => onSelectParent(details.parent)}
            />
          </div>
        )}
      </div>
      <ShortcutsTooltip resourceUris={uris} />
      <IconButton
        ariaLabel={t(labelCopyLink)}
        size="small"
        title={t(labelCopyLink)}
        onClick={copyResourceLink}
      >
        <CopyIcon fontSize="small" />
      </IconButton>
    </>
  );
};

const Header = ({ details, onSelectParent }: Props): JSX.Element => {
  const classes = useStyles({
    displaySeverity: not(isNil(details?.severity_level)),
  });

  return (
    <div className={classes.header}>
      <HeaderContent details={details} onSelectParent={onSelectParent} />
    </div>
  );
};

export default memoizeComponent<Props>({
  Component: Header,
  memoProps: ['details'],
});
