import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { hasPath } from 'ramda';

import { Grid, Typography, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';
import CopyIcon from '@material-ui/icons/FileCopy';

import {
  StatusChip,
  SeverityCode,
  IconButton,
  useSnackbar,
  Severity,
} from '@centreon/ui';
import copyToClipboard from '@centreon/ui/src/utils/copy';

import {
  labelCopyLink,
  labelLinkCopied,
  labelSomethingWentWrong,
} from '../translatedLabels';
import memoizeComponent from '../memoizedComponent';

import { DetailsSectionProps } from '.';

const useStyles = makeStyles((theme) => ({
  header: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: 'auto minmax(0, 1fr) auto',
    height: 43,
    padding: theme.spacing(0, 1),
  },
  parent: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto minmax(0, 1fr)',
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

const HeaderContent = ({ details }: DetailsSectionProps): JSX.Element => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();
  const classes = useStyles();

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

  return (
    <>
      {details.severity_level && (
        <StatusChip
          label={details.severity_level.toString()}
          severityCode={SeverityCode.None}
        />
      )}
      <StatusChip
        label={t(details.status.name)}
        severityCode={details.status.severity_code}
      />
      <div>
        <Typography className={classes.truncated}>{details.name}</Typography>
        {hasPath(['parent', 'status'], details) && (
          <div className={classes.parent}>
            <StatusChip
              severityCode={
                details.parent.status?.severity_code || SeverityCode.None
              }
            />
            <Typography className={classes.truncated} variant="caption">
              {details.parent.name}
            </Typography>
          </div>
        )}
      </div>
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

const Header = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.header}>
      <HeaderContent details={details} />
    </div>
  );
};

export default memoizeComponent<DetailsSectionProps>({
  Component: Header,
  memoProps: ['details'],
});
